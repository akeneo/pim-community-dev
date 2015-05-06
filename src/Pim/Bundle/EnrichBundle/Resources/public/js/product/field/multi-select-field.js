'use strict';

define(
    [
        'jquery',
        'pim/field',
        'underscore',
        'text!pim/template/product/field/multi-select',
        'text!pim/template/product/tab/attribute/add-option-button',
        'routing',
        'pim/attribute-option/create',
        'jquery.select2'
    ],
    function ($, Field, _, fieldTemplate, addOptionButtonTemplate, Routing, createOption) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            addOptionButtonTemplate: _.template(addOptionButtonTemplate),
            fieldType: 'multi-select',
            events: {
                'change input': 'updateModel',
                'click .add-attribute-option': 'createOption'
            },
            initialize: function () {
                Field.prototype.initialize.apply(this, arguments);

                this.addElement('footer', 'add_option', this.addOptionButtonTemplate());

                return this;
            },
            createOption: function () {
                createOption(this.attribute).done(_.bind(function (option) {
                    var value = this.getCurrentValue().value;
                    value.push(option.code);
                    this.setCurrentValue(value);
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
                        var choices = _.map($(element).val().split(','), function (choice) {
                            return {
                                id: choice,
                                text: choice
                            };
                        });
                        callback(choices);
                    },
                    multiple: true
                });
            },
            updateModel: function (event) {
                var data = event.currentTarget.value.split(',');
                this.setCurrentValue(data);
            }
        });
    }
);
