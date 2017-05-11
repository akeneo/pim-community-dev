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
        'pim/template/product/status-switcher'
    ],
    function (
        _,
        mediator,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'AknDropdownButton AknDropdown status-switcher',
            template: _.template(template),
            events: {
                'click .AknDropdown-menuLink': 'updateStatus'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var status = this.getRoot().getFormData().enabled;

                this.$el.html(this.template({
                    status: status
                }));
                this.$el
                    .addClass(status ? 'AknDropdownButton--apply' : 'AknDropdownButton--important')
                    .removeClass(status ? 'AknDropdownButton--important' : 'AknDropdownButton--apply')
                    .find('.AknCaret')
                    .addClass(status ? 'AknCaret--apply' : 'AknCaret--important')
                    .removeClass(status ? 'AknCaret--important' : 'AknCaret--apply');
                this.delegateEvents();

                return this;
            },

            /**
             * Update the current status of the product
             *
             * @param {Event} event
             */
            updateStatus: function (event) {
                var newStatus = event.currentTarget.dataset.status === 'enable';
                this.getFormModel().set('enabled', newStatus);
                this.getRoot().trigger('pim_enrich:form:entity:update_state');
                this.render();
            }
        });
    }
);
