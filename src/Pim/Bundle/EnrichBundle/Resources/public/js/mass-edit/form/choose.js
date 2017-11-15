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
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/mass-edit/choose'
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
            className: 'AknSquareList',
            events: {
                'click .operation': 'updateOperation'
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
                    currentOperation: this.getParent().getCurrentOperation()
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
                this.getParent().setCurrentOperation($(event.target).closest('.operation').data('code'));
                this.render();
            },

            /**
             * {@inheritdoc}
             */
            getLabel: function () {
                const itemsCount = this.getFormData().itemsCount;

                return __(this.config.title, {itemsCount}, itemsCount);
            },

            /**
             * Returns the title of the operation
             *
             * @returns {string}
             */
            getTitle() {
                return __(this.config.title);
            },

            /**
             * Returns the label with the count of impacted elements
             *
             * @returns {String}
             */
            getLabelCount: function () {
                const itemsCount = this.getFormData().itemsCount;

                return __(this.config.labelCount, {itemsCount}, itemsCount);
            },

            /**
             * {@inheritdoc}
             */
            getDescription: function () {
                return '';
            },

            /**
             * {@inheritdoc}
             */
            getIllustrationClass: function () {
                return '';
            }
        });
    }
);
