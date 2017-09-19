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
        'pim/router',
        'oro/messenger'
    ],
    function (
        $,
        _,
        __,
        BaseLaunch,
        router,
        messenger
    ) {
        return BaseLaunch.extend({
            /**
             * {@inherit}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:job:file_updated', this.render.bind(this));

                return BaseLaunch.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inherit}
             */
            launch: function () {
                if (this.getFormData().file) {
                    var formData = new FormData();
                    formData.append('file', this.getFormData().file);

                    router.showLoadingMask();

                    $.ajax({
                        url: this.getUrl(),
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        cache: false,
                        processData: false
                    })
                    .then((response) => {
                        router.redirect(response.redirectUrl);
                    })
                    .fail(() => {
                        messenger.notify('error', __('pim_enrich.form.job_instance.fail.launch'));
                    })
                    .always(router.hideLoadingMask());
                }
            },

            /**
             * {@inherit}
             */
            isVisible: function () {
                return $.Deferred().resolve(this.getFormData().file).promise();
            }
        });
    }
);
