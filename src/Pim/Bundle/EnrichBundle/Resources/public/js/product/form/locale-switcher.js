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
            className: 'btn-group locale-switcher',
            events: {
                'click li a': 'changeLocale',
            },
            render: function () {
                ChannelManager.getLocales().done(_.bind(function(locales) {
                    if (!this.getParent().getLocale()) {
                        this.getParent().setLocale(locales[0]);
                    }
                    this.$el.html(
                        this.template({
                            locales: locales,
                            currentLocale: this.getParent().getLocale()
                        })
                    );
                    this.delegateEvents();
                }, this));

                return this;
            },
            changeLocale: function (event) {
                this.getParent().setLocale(event.currentTarget.dataset.locale);
                this.render();
            }
        });
    }
);
