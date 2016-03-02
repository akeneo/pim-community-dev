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
        'pim/fetcher-registry'
    ],
    function (_, BaseForm, template, FetcherRegistry) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group scope-switcher',
            events: {
                'click li a': 'changeScope'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('channel')
                    .fetchAll()
                    .done(function (channels) {
                        if (!this.getParent().getScope()) {
                            this.getParent().setScope(channels[0].code, {silent: true});
                        }

                        var scope = _.findWhere(channels, { code: this.getParent().getScope() });

                        this.$el.html(
                            this.template({
                                channels: channels,
                                currentScope: scope.label
                            })
                        );
                        this.delegateEvents();
                    }.bind(this)
                );

                return this;
            },

            /**
             * Set the current selected scope
             *
             * @param {Event} event
             */
            changeScope: function (event) {
                this.getParent().setScope(event.currentTarget.dataset.scope);
                this.render();
            }
        });
    }
);
