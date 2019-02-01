'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'pim/form-builder',
        'oro/messenger',
        'pim/template/attribute-option/validation-error'
    ],
    function (
        $,
        _,
        Backbone,
        Routing,
        FormBuilder,
        messenger,
        errorTemplate
    ) {
        var CreateOptionView = Backbone.View.extend({
            errorTemplate: _.template(errorTemplate),
            attribute: null,

            initialize: function (options) {
                this.attribute = options.attribute;
            },
            createOption: function () {
                var deferred = $.Deferred();

                FormBuilder.build('pim-attribute-option-form').done((form) => {
                    var modal = new Backbone.BootstrapModal({
                        title: _.__('pim_enrich.entity.product.module.attribute.add_attribute_option'),
                        content: form,
                        cancelText: _.__('pim_common.cancel'),
                        okText: _.__('pim_common.add'),
                        picture: 'illustrations/Attribute.svg',
                        okCloses: false
                    });
                    modal.open();

                    modal.on('cancel', deferred.reject);
                    modal.on('ok', () => {
                        $.ajax({
                            method: 'POST',
                            url: Routing.generate(
                                'pim_enrich_attributeoption_create',
                                { attributeId: this.attribute.meta.id }
                            ),
                            data: JSON.stringify(form.getFormData())
                        }).done((option) => {
                            modal.close();
                            messenger.notify(
                                'success',
                                _.__('pim_enrich.entity.attribute_option.flash.create.success')
                            );
                            deferred.resolve(option);
                        }).fail((xhr) => {
                            var response = xhr.responseJSON;

                            if (response.code) {
                                form.$('input[name="code"]').after(
                                    this.errorTemplate({
                                        errors: [response.code]
                                    })
                                );
                            } else {
                                messenger.notify(
                                    'error',
                                    _.__('pim_enrich.entity.attribute_option.flash.create.fail')
                                );
                            }
                        });
                    });
                });

                return deferred.promise();
            }
        });

        return function (attribute) {
            if (!attribute) {
                throw new Error('Attribute must be provided to create a new option');
            }

            var view = new CreateOptionView({ attribute: attribute });

            return view.createOption().always(function () {
                view.remove();
            });
        };
    }
);
