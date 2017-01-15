'use strict';

/**
 * Module used to display the category general properties field of a channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/channel/tab/properties/general/category-tree',
        'pim/user-context',
        'jquery.select2'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template,
        UserContext
    ) {
        return BaseForm.extend({
            className: 'AknFieldContainer',
            template: _.template(template),
            catalogLocale: UserContext.get('catalogLocale'),

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                FetcherRegistry.getFetcher('category').fetchAll().then(function (categories) {

                    var data = this.getFormData();
                    if ('' === data.category_tree) {
                        data.category_tree = categories[0].code;
                        this.setData(data);
                    }

                    this.$el.html(this.template({
                        categoryTree: data.category_tree,
                        categories: categories,
                        catalogLocale: this.catalogLocale,
                        label: __('pim_enrich.form.channel.tab.properties.category_tree'),
                        requiredLabel: __('pim_enrich.form.required'),
                        errors: this.getParent().getValidationErrorsForField('category_tree')
                    }));

                    this.$('.select2').select2().on('change', this.updateState.bind(this));

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Sets new category tree on change.
             *
             * @param {Object} event
             */
            updateState: function (event) {
                this.setCategory($(event.target).val());
            },

            /**
             * Sets specified category tree into root model.
             *
             * @param {Array} code
             */
            setCategory: function (code) {
                var data = this.getFormData();

                data.category_tree = code;
                this.setData(data);
            }
        });
    }
);
