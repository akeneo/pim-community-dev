'use strict';
/**
 * Choose extension for mass edit
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/mass-edit/choose'
    ],
    function (
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknChoicesField',
            events: {
                'change .operation': 'updateOperation'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = _.extend({}, meta.config);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    operations: this.getParent().getOperations(),
                    currentOperation: this.getParent().getCurrentOperation(),
                    __: __
                }));

                this.delegateEvents();

                return this;
            },

            /**
             * Update the mass edit model
             *
             * @param {Event} event
             */
            updateOperation: function (event) {
                this.getParent().setCurrentOperation(event.target.value)
            },

            /**
             * {@inheritdoc}
             */
            getLabel: function () {
                return __(
                    this.config.title,
                    {itemsCount: this.getFormData().itemsCount}
                );
            },

            /**
             * {@inheritdoc}
             */
            getDescription: function () {
                return '';
            }
        });
    }
);
