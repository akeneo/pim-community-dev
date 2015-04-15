'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/locale-switcher',
        'pim/config-manager',
        'pim/user-context',
        'pim/i18n'
    ],
    function (_, BaseForm, template, ConfigManager, usercontext, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group locale-switcher',
            events: {
                'click li a': 'changeLocale'
            },
            render: function () {
                ConfigManager.getEntityList('locales').done(_.bind(function (locales) {
                    if (!this.getParent().getLocale()) {
                        this.getParent().setLocale(usercontext.getUserContext().get('catalogLocale'), {silent: true});
                    }
                    this.$el.html(
                        this.template({
                            locales: locales,
                            currentLocale: _.findWhere(locales, {code: this.getParent().getLocale()}),
                            i18n: i18n
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
