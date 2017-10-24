/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form/common/fields/field',
        'oro/translator',
        'pim/i18n',
        'pim/initselect2',
        'pim/user-context',
        'pim/template/form/common/fields/simple-select-async'
    ],
    function (
        $,
        _,
        BaseField,
        __,
        i18n,
        initSelect2,
        UserContext,
        template
    ) {
        return BaseField.extend({
            events: {
                'change input': function (event) {
                    this.errors = [];
                    this.updateModel(this.getFieldValue(event.target));
                    this.getRoot().render();
                }
            },
            template: _.template(template),
            choiceUrl: null,

            initialize() {
                this.choiceUrl = null;

                return BaseField.prototype.initialize.apply(this, arguments);
            },

            setChoiceUrl(choiceUrl) {
                this.choiceUrl = choiceUrl;
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function (templateContext) {
                return this.template(_.extend(templateContext, {
                    value: this.getFormData()[this.fieldName]
                }));
            },

            /**
             * {@inheritdoc}
             */
            postRender() {
                const options = {
                    ajax: {
                        url: this.choiceUrl,
                        cache: true,
                        data: (term, page) => {
                            return {
                                search: term,
                                options: {
                                    limit: 20,
                                    page: page,
                                    catalogLocale: UserContext.get('catalogLocale')
                                }
                            };
                        },
                        results: (response) => {
                            if (response.results) {
                                response.more = 20 === Object.keys(response.results).length;

                                return response;
                            }

                            return {
                                more: 20 === Object.keys(response).length,
                                results: response.map((item) => this.convertBackendItem(item))
                            };
                        }
                    },
                    initSelection: (element, callback) => {
                        const id = $(element).val();
                        if ('' !== id) {
                            $.get(this.choiceUrl, {options: {identifiers: [id]}})
                                .then((response) => {
                                    let selected = _.findWhere(response, {code: id});

                                    if (!selected) {
                                        selected = _.findWhere(response.results, {id: id});
                                    } else {
                                        selected = this.convertBackendItem(selected);
                                    }
                                    callback(selected);
                                });
                        }
                    },
                    placeholder: ' '
                };

                initSelect2.init(this.$('.select2'), options);
            },

            /**
             * Converts the item returned from the backend to fit select2 needs.
             *
             * @param {Object} item
             *
             * @returns {Object}
             */
            convertBackendItem(item) {
                return {
                    id: item.code,
                    text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code)
                };
            },

            /**
             * @param {Element} field
             *
             * @returns {String}
             */
            getFieldValue(field) {
                return $(field).val();
            }
        });
    });
