'use strict';

/**
 * Module used to display when no attribute are available
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/variant-group/form/no-attribute'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknAttributeActions-help no-attribute',

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:to-fill-filter', this.addFieldFilter);

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var variantGroup = this.getFormData();
                this.$el.empty();

                if (_.isEmpty(variantGroup.values)) {
                    this.$el.append(this.template({
                        label: __('pim_enrich.entity.variant_group.info.no_attributes')
                    }));
                }

                return this;
            },

            /**
             * Add filter on field to make it readonly.
             *
             * @param {object} event
             */
            addFieldFilter: function (event) {
                event.filters.push($.Deferred().resolve(function () {
                    return [];
                }));
            }
        });
    }
);
