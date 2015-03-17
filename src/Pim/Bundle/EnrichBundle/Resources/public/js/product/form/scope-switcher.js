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
            className: 'btn-group pull-right',
            id: 'current-scope',
            events: {
                'click li a': 'changeScope',
            },
            render: function () {
                ConfigManager.getEntityList('channels').done(_.bind(function(channels) {
                    this.$el.html(
                        this.template({
                            channels: channels,
                            currentScope: this.getRoot().state.get('scope') || channels[0].code
                        })
                    );
                    this.$el.prependTo(this.getRoot().$('.tab-content>header'));
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
