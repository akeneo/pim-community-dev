'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/locale-switcher',
        'pim/channel-manager'
    ],
    function(_, BaseForm, template, ChannelManager) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group',
            id: 'current-locale',
            events: {
                'click li a': 'changeLocale',
            },
            render: function () {
                ChannelManager.getLocales().done(_.bind(function(locales) {
                    if (!this.getRoot().state.get('locale')) {
                        this.getRoot().state.set('locale', locales[0]);
                    }
                    this.$el.html(
                        this.template({
                            locales: locales,
                            currentLocale: this.getRoot().state.get('locale')
                        })
                    );
                    this.delegateEvents();
                }, this));

                return this;
            },
            changeLocale: function (event) {
                this.getRoot().state.set('locale', event.currentTarget.dataset.locale);
                this.render();
            }
        });
    }
);
