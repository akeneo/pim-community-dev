'use strict';
/**
 * Multi select field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'pim/field',
        'underscore',
        'text!pim/template/product/field/multi-select',
        'routing',
        'pim/attribute-option/create',
        'pim/security-context',
        'jquery.select2'
    ],
    function ($, Field, _, fieldTemplate, Routing, createOption, SecurityContext) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            choicePromise: null,
            events: {
                'change .field-input:first input.select-field': 'updateModel',
                'click .add-attribute-option': 'createOption'
            },

            /**
             * {@inheritdoc}
             */
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments).then(function (templateContext) {
                    templateContext.userCanAddOption = SecurityContext.isGranted('pim_enrich_attribute_edit');

                    return templateContext;
                });
            },

            /**
             * Create a new option for this multi select field
             */
            createOption: function () {
                if (!SecurityContext.isGranted('pim_enrich_attribute_edit')) {
                    return;
                }
                createOption(this.attribute).then(function (option) {
                    if (this.isEditable()) {
                        var value = this.getCurrentValue().data;
                        value.push(option.code);
                        this.setCurrentValue(value);
                    }

                    this.choicePromise = null;
                    this.render();
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },

            /**
             * {@inheritdoc}
             */
            postRender: function () {
                this.$('[data-toggle="tooltip"]').tooltip();
                this.getChoiceUrl().then(function (choiceUrl) {
                    this.$('input.select-field').select2('destroy').select2({
                        ajax: {
                            url: choiceUrl,
                            cache: true,
                            data: function (term) {
                                return {search: term};
                            },
                            results: function (data) {
                                return data;
                            }
                        },
                        initSelection: function (element, callback) {
                            if (null === this.choicePromise) {
                                this.choicePromise = $.get(choiceUrl);
                            }

                            this.choicePromise.then(function (response) {
                                var results = response.results;
                                var choices = _.map($(element).val().split(','), function (choice) {
                                    return _.findWhere(results, {id: choice});
                                });
                                callback(choices);
                            });
                        }.bind(this),
                        multiple: true
                    });
                }.bind(this));
            },

            /**
             * Get the URL to retrieve the choice list for this select field
             *
             * @returns {Promise}
             */
            getChoiceUrl: function () {
                return $.Deferred().resolve(
                    Routing.generate(
                        'pim_ui_ajaxentity_list',
                        {
                            'class': 'PimCatalogBundle:AttributeOption',
                            'dataLocale': this.context.locale,
                            'collectionId': this.attribute.id,
                            'options': {'type': 'code'}
                        }
                    )
                ).promise();
            },

            /**
             * {@inheritdoc}
             */
            updateModel: function () {
                var data = this.$('.field-input:first input.select-field').val().split(',');
                if (1 === data.length && '' === data[0]) {
                    data = [];
                }

                this.choicePromise = null;

                this.setCurrentValue(data);
            }
        });
    }
);
