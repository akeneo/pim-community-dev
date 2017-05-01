'use strict';
/**
 * Upload and launch button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/job/common/edit/launch',
        'oro/navigation',
        'oro/messenger',
        'oro/loading-mask'
    ],
    function ($, _, __, BaseLaunch, Navigation, messenger, LoadingMask) {
        return BaseLaunch.extend({
            /**
             * {@inherit}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:job:file_updated', this.render.bind(this));

                return BaseLaunch.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    label: __(this.getFormData().file ? this.config.upload : this.config.launch)
                }));

                return this;
            },

            /**
             * Launch the job
             */
            launch: function () {
                if (this.getFormData().file) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo(this.$el.parent());
                    loadingMask.show();

                    var formData = new FormData();
                    formData.append('file', this.getFormData().file);

                    $.ajax({
                        url: this.getUrl(),
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        cache: false,
                        processData: false
                    })
                    .then(function (response) {
                        loadingMask.hide();
                        Navigation.getInstance().setLocation(response.redirectUrl);
                    }.bind(this))
                    .fail(function () {
                        loadingMask.hide();
                        messenger.notificationFlashMessage('error', __('pim_enrich.form.job_instance.fail.launch'));
                    });
                } else {
                    $.post(this.getUrl(), {method: 'POST'}).
                        then(function (response) {
                            Navigation.getInstance().setLocation(response.redirectUrl);
                        })
                        .fail(function () {
                            messenger.notificationFlashMessage('error', __('pim_enrich.form.job_instance.fail.launch'));
                        });
                }

            }
        });
    }
);
