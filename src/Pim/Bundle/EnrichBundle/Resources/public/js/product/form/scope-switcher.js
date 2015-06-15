'use strict';
/**
 * Scope switcher extension
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
        'text!pim/template/product/scope-switcher',
        'pim/entity-manager'
    ],
    function (_, BaseForm, template, EntityManager) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group scope-switcher',
            events: {
                'click li a': 'changeScope'
            },
            render: function () {
                EntityManager.getRepository('channel')
                    .findAll()
                    .done(_.bind(function (channels) {
                        if (!this.getParent().getScope()) {
                            this.getParent().setScope(channels[0].code, {silent: true});
                        }

                        this.$el.html(
                            this.template({
                                channels: channels,
                                currentScope: this.getParent().getScope()
                            })
                        );
                        this.delegateEvents();
                    }, this)
                );

                return this;
            },
            changeScope: function (event) {
                this.getParent().setScope(event.currentTarget.dataset.scope);
                this.render();
            }
        });
    }
);
