'use strict';
/**
 * Displays a start copy button
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/product/start-copy'
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
            className: 'AknDropdown-menuLink start-copying',
            events: {
                'click': 'startCopy'
            },
            isCopying: false,

            /**
             * {@inheritdoc}
             */
            configure() {
                this.listenTo(this.getRoot(), 'pim_enrich:form:stop_copy', this.stopCopy.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html('');
                if (!this.isCopying) {
                    this.$el.html(this.template({
                        label: __('pim_enrich.entity.product.module.copy.label')
                    }));
                }
            },

            /**
             * Triggers a new event to start copy
             */
            startCopy() {
                this.isCopying = true;
                this.getRoot().trigger('pim_enrich:form:start_copy');
                this.render();
            },

            /**
             * Stops the copy and re-display the button
             */
            stopCopy() {
                this.isCopying = false;
                this.render();
            }
        });
    }
);
