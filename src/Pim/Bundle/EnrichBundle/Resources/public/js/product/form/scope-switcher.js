'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/scope-switcher',
        'pim/channel-manager'
    ],
    function(_, BaseForm, template, ChannelManager) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group',
            id: 'current-scope',
            events: {
                'click li a': 'changeScope',
            },
            render: function () {
                ChannelManager.getChannels().done(_.bind(function(channels) {
                    if (!this.getRoot().state.get('scope')) {
                        this.getRoot().state.set('scope', channels[0].code);
                    }
                    this.$el.html(
                        this.template({
                            channels: channels,
                            currentScope: this.getRoot().state.get('scope')
                        })
                    );
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
