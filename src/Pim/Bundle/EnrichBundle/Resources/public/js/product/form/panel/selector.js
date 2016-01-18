'use strict';
/**
 * Panel selector extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/panel/selector'
    ],
    function (_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-selector btn-group',
            events: {
                'click button': 'changePanel'
            },
            render: function () {
                this.$el.html(
                    this.template({
                        state: this.getParent().state.toJSON()
                    })
                );

                this.delegateEvents();

                return this;
            },
            changePanel: function (event) {
                if (this.getParent().state.get('currentPanel') === event.currentTarget.dataset.panel) {
                    this.getParent().state.set('currentPanel', null);
                } else {
                    this.getParent().state.set('currentPanel', event.currentTarget.dataset.panel);
                }

                this.getParent().closeFullPanel();
            }
        });
    }
);
