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
        'text!pim/template/product/field/simple-select',
        'routing',
        'pim/attribute-option/create',
        'pim/security-context',
        'pim/initselect2',
        'pim/user-context'
    ],
    function ($, Field, _, fieldTemplate, Routing, createOption, SecurityContext, initSelect2, UserContext) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            choicePromise: null,
            events: {
                'change .field-input:first input[type="hidden"].select-field': 'updateModel',
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
                this.choicePromise = null;
                this.getChoiceUrl().then(function (choiceUrl) {
                    var options = {
                        ajax: {
                            url: choiceUrl,
                            cache: true,
                            data: function (term) {
                                return {
                                    search: term,
                                    options: {
                                        locale: UserContext.get('catalogLocale')
                                    }
                                };
                            },
                            results: function (data) {
                                return data;
                            }
                        },
                        initSelection: function (element, callback) {
                            var id = $(element).val();
                            if ('' !== id) {
                                if (null === this.choicePromise) {
                                    this.choicePromise = $.get(choiceUrl);
                                }

                                this.choicePromise.then(function (response) {
                                    var selected = _.findWhere(response.results, {id: id});
                                    callback(selected);
                                });
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
                var data = this.$('.field-input:first input[type="hidden"].select-field').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.choicePromise = null;

                this.setCurrentValue(data);
            }
        });
    }
);
