'use strict';

define(
    [
        'jquery',
        'pim/field',
        'underscore',
        'text!pim/template/product/field/simple-select',
        'routing',
        'pim/attribute-option/create',
        'pim/security-context',
        'jquery.select2'
    ],
    function ($, Field, _, fieldTemplate, Routing, createOption, SecurityContext) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'simple-select',
            events: {
                'change input': 'updateModel',
                'click .add-attribute-option': 'createOption'
            },
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments).then(function (templateContext) {
                    templateContext.userCanAddOption = SecurityContext.isGranted('pim_enrich_attribute_edit');

                    return templateContext;
                });
            },
            createOption: function () {
                if (!SecurityContext.isGranted('pim_enrich_attribute_edit')) {
                    return;
                }
                createOption(this.attribute).done(_.bind(function (option) {
                    this.setCurrentValue(option.code);
                    this.render();
                }, this));
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            render: function () {
                Field.prototype.render.apply(this, arguments);

                var $elem = this.$('input.select-field');

                $elem.select2('destroy').select2({
                    ajax: {
                        url: Routing.generate(
                            'pim_ui_ajaxentity_list',
                            {
                                'class': 'PimCatalogBundle:AttributeOption',
                                'dataLocale': this.context.locale,
                                'collectionId': this.attribute.id,
                                'options': {'type': 'code'}
                            }
                        ),
                        cache: true,
                        data: function (term) {
                            return {search: term};
                        },
                        results: function (data) {
                            return data;
                        }
                    },
                    initSelection: function (element, callback) {
                        var id = $(element).val();
                        if (id !== '') {
                            callback({'id': id, 'text': id});
                        }
                    },
                    placeholder: ' ',
                    allowClear: true
                });
            },
            updateModel: function (event) {
                var data = event.currentTarget.value;
                this.setCurrentValue(data);
            }
        });
    }
);
