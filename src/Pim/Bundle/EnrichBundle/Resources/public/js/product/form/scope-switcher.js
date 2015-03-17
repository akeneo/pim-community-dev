'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/scope-switcher',
        'pim/config-manager'
    ],
    function(_, BaseForm, template, ConfigManager) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group',
            id: 'current-scope',
            events: {
                'click li a': 'changeScope',
            },
            render: function () {
                ConfigManager.getEntityList('channels').done(_.bind(function(channels) {
                    if (!this.getRoot().state.get('scope')) {
                        this.getRoot().state.set('scope', channels[0].code);
                    }
                    this.$el.html(
                        this.template({
                            channels: channels,
                            currentScope: this.getRoot().state.get('scope')
                        })
                    );
                    this.$el.prependTo(this.getParent().$('.tab-content > header > .attribute-edit-actions'));
                    this.delegateEvents();
                }, this));

                return this;
            },
            changeScope: function (event) {
                this.getRoot().state.set('scope', event.currentTarget.dataset.scope);
                this.render();
            }
        });
    }
);
