'use strict';
/**
 * Simple select field
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
        'pim/template/product/field/simple-select',
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
            promiseIdentifier: null,
            choiceUrl: null,
            events: {
                'change .field-input:first input[type="hidden"].select-field': 'updateModel',
                'click .add-attribute-option': 'createOption'
            },

            /**
             * {@inheritdoc}
             */
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments).then(function (templateContext) {
                    var isAllowed = SecurityContext.isGranted('pim_enrich_attribute_edit');
                    templateContext.userCanAddOption = this.editable && isAllowed;

                    return templateContext;
                }.bind(this));
            },

            /**
             * Create a new option for this simple select field
             */
            createOption: function () {
                if (!SecurityContext.isGranted('pim_enrich_attribute_edit')) {
                    return;
                }

                createOption(this.attribute).then(function (option) {
                    if (this.isEditable()) {
                        this.setCurrentValue(option.code);
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
                            url: choiceUrl,
                            cache: true,
                            data: function (term, page) {
                                return {
                                    search: term,
                                    options: {
                                        limit: 20,
                                        page: page,
                                        catalogLocale: UserContext.get('catalogLocale')
                                    }
                                };
                            },
                            results: function (response) {
                                if (response.results) {
                                    response.more = 20 === _.keys(response.results).length;

                                    return response;
                                }

                                var data = {
                                    more: 20 === _.keys(response).length,
                                    results: []
                                };
                                _.each(response, function (value) {
                                    data.results.push(this.convertBackendItem(value));
                                }.bind(this));

                                return data;
                            }.bind(this)
                        },
                        initSelection: function (element, callback) {
                            var id = $(element).val();
                            if ('' !== id) {
                                if (
                                    null === this.choicePromise
                                    || this.promiseIdentifier !== id
                                    || this.choiceUrl !== choiceUrl
                                ) {
                                    this.choiceUrl = choiceUrl;
                                    this.choicePromise = $.get(choiceUrl, {options: {identifiers: [id]}});
                                    this.promiseIdentifier = id;
                                }

                                this.choicePromise.then(function (response) {
                                    var selected = _.findWhere(response, {code: id});

                                    if (!selected) {
                                        selected = _.findWhere(response.results, {id: id});
                                    } else {
                                        selected = this.convertBackendItem(selected);
                                    }
                                    callback(selected);
                                }.bind(this));
                            }
                        }.bind(this),
                        placeholder: ' ',
                        allowClear: true
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
                            identifier: this.attribute.code
                        }
                    )
                ).promise();
            },

            /**
             * {@inheritdoc}
             */
            updateModel: function () {
                var data = this.$('.field-input:first input[type="hidden"].select-field').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.choicePromise = null;

                this.setCurrentValue(data);
            },

            /**
             * Convert the item returned from the backend to fit select2 needs
             *
             * @param {object} item
             *
             * @return {object}
             */
            convertBackendItem: function (item) {
                return {
                    id: item.code,
                    text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code)
                };
            }
        });
    }
);
