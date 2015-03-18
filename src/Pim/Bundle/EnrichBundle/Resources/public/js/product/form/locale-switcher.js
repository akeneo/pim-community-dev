'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/locale-switcher',
        'pim/config-manager'
    ],
    function(_, BaseForm, template, ConfigManager) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group',
            id: 'current-locale',
            events: {
                'click li a': 'changeLocale',
            },
            getLocales: function() {
                var promise = $.Deferred();

                ConfigManager.getEntityList('channels').done(function(channels) {
                    var locales = _.unique(_.flatten(_.pluck(channels, 'locales')));
                    promise.resolve(locales);
                });

                return promise.promise();
            },
            render: function () {
                this.getLocales().done(_.bind(function(locales) {
                    if (!this.getRoot().state.get('locale')) {
                        this.getRoot().state.set('locale', locales[0]);
                    }
                    this.$el.html(
                        this.template({
                            locales: locales,
                            currentLocale: this.getRoot().state.get('locale')
                        })
                    );
                    this.$el.prependTo(this.getParent().$('.tab-content > header > .attribute-edit-actions'));
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
