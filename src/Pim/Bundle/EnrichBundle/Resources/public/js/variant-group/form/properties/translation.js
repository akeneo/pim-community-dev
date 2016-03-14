'use strict';

/**
 * Module used to display the localized properties of a variant group
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/variant-group/tab/properties/translation'
    ],
    function (
        _,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'accordion-group',
            template: _.template(template),
            events: {
                'change .label-field': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('locale').fetchAll().then(function (locales) {
                    this.$el.html(this.template({
                        model: this.getFormData(),
                        locales: locales
                    }));
                }.bind(this));

                this.renderExtensions();
            },

            /**
             * @param {Object} event
             */
            updateModel: function (event) {
                var data = this.getFormData();

                data.labels[event.target.dataset.locale] = event.target.value;

                this.setData(data);
            }
        });
    }
);
