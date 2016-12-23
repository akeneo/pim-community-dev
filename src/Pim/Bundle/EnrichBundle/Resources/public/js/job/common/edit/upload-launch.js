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
        'underscore',
        'oro/translator',
        'pim/job/common/edit/launch',
        'routing',
        'oro/navigation',
        'pim/common/property'
    ],
    function (_, __, BaseLaunch, Routing, Navigation, propertyAccessor, template) {
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
                    __: __,
                    label: !this.getFormData().file ? this.config.launch : this.config.upload
                }));

                return this;
            },

            /**
             * Launch the job
             */
            launch: function () {
                if (this.getFormData().file) {
                    var formData = new FormData();
                    formData.append('file', this.getFormData().file);

                    $.ajax({
                        url: this.getUrl(),
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        cache: false,
                        processData: false
                    })
                    .done(function (response) {
                        Navigation.getInstance().setLocation(response.redirectUrl);
                    }.bind(this))
                    .fail(function (xhr) {
                        // var message = xhr.responseJSON && xhr.responseJSON.message ?
                        //     xhr.responseJSON.message :
                        //     _.__('pim_enrich.entity.product.error.upload');
                        // navigation.addFlashMessage('error', message);
                        // navigation.afterRequest();
                    });
                } else {
                    $.ajax(this.getUrl(), {method: 'POST'}).
                        then(function (response) {
                            Navigation.getInstance().setLocation(response.redirectUrl);
                        })
                        .fail(function () {

                        });
                }

            }
        });
    }
);
