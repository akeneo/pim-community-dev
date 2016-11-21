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
        'pim/initselect2',
        'pim/user-context',
        'pim/i18n'
    ],
    function ($, Field, _, fieldTemplate, Routing, createOption, SecurityContext, initSelect2, UserContext, i18n) {
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
                    var options = {
                        ajax: {
                            url: Routing.generate('pim_enrich_attributeoption_get', {identifier: this.attribute.id}),
                            quietMillis: 250,
                            cache: true,
                            data: function (term, page) {
                                return {
                                    search: term,
                                    options: {
                                        limit: 20,
                                        page: page
                                    }
                                };
                            }.bind(this),
                            results: function (response) {
                                var data = {
                                    more: 20 === _.keys(response).length,
                                    results: []
                                };
                                _.each(response, function (value) {
                                    data.results.push({
                                        id: value.code,
                                        text: i18n.getLabel(value.labels, UserContext.get('catalogLocale'), value.code)
                                    });
                                });

                                return data;
                            }
                        },
                        initSelection: function (element, callback) {
                            if (null === this.choicePromise) {
                                this.choicePromise = $.get(choiceUrl);
                            }

                            this.choicePromise.then(function (results) {
                                var choices = _.map($(element).val().split(','), function (choice) {
                                    var option = _.findWhere(results, {code: choice});
                                    if (option) {
                                        return {
                                            id: option.code,
                                            text: i18n.getLabel(
                                                option.labels,
                                                UserContext.get('catalogLocale'),
                                                option.code
                                            )
                                        };
                                    }
                                });
                                callback(choices);
                            });
                        }.bind(this),
                        multiple: true
                    };

                    initSelect2.init(this.$('input.select-field'), options);
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
                        'pim_enrich_attributeoption_get',
                        {
                            identifier: this.attribute.id,
                            options: {
                                identifiers: this.model.attributes.values[0].data
                            }
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
