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
        'pim/template/product/panel/selector'
    ],
    function (_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknButtonList panel-selector btn-group',
            events: {
                'click button': 'changePanel'
            },
            render: function () {
                this.$el.html(
                    this.template({
                        panels: this.getParent().panels,
                        currentPanel: this.getParent().getCurrentPanelCode()
                    })
                );

                this.delegateEvents();

                return this;
            },
            changePanel: function (event) {
                if (this.getParent().getCurrentPanelCode() === event.currentTarget.dataset.panel) {
                    this.getParent().setCurrentPanelCode(null);
                } else {
                    this.getParent().setCurrentPanelCode(event.currentTarget.dataset.panel);
                }

                this.getParent().closeFullPanel();
            }
        });
    }
);
