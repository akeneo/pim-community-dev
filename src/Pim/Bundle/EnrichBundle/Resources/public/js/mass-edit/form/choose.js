'use strict';
/**
 * Edit form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alps <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/mass-edit/choose',
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

            initialize: function (meta) {
                this.config = _.extend({}, meta.config);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            render: function () {
                this.$el.html(this.template({
                    operations: this.getParent().getOperations(),
                    currentOperation: this.getParent().getCurrentOperation(),
                    __: __
                }));

                this.delegateEvents();

                return this;
            },

            updateOperation: function (event) {
                this.getParent().setCurrentOperation(event.target.value)
            },

            getLabel: function () {
                return __(
                    this.config.title,
                    {itemsCount: this.getFormData().itemsCount}
                );
            },

            getDescription: function () {
                return '';
            }
        });
    }
);
