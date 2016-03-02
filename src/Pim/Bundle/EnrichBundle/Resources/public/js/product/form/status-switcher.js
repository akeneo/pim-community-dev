'use strict';
/**
 * Status switcher extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/mediator',
        'pim/form',
        'text!pim/template/product/status-switcher'
    ],
    function (
        _,
        mediator,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'btn-group status-switcher btn-dropdown',
            template: _.template(template),
            events: {
                'click li a': 'updateStatus'
            },
            render: function () {
                var status = this.getRoot().getFormData().enabled;

                this.$el.html(this.template({
                    status: status
                }));
                this.$el.addClass(status ? 'enabled' : 'disabled');
                this.$el.removeClass(status ? 'disabled' : 'enabled');
                this.delegateEvents();

                return this;
            },
            updateStatus: function (event) {
                var newStatus = event.currentTarget.dataset.status === 'enable';
                this.getFormModel().set('enabled', newStatus);
                this.getRoot().trigger('pim_enrich:form:entity:update_state');
                this.render();
            }
        });
    }
);
