"use strict";

define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'oro/loading-mask',
        'oro/template/system/tab/localization',
        'pim/initselect2'
    ],
    function(
        _,
        __,
        BaseForm,
        FetcherRegistry,
        LoadingMask,
        template,
        initSelect2
    ) {
        return BaseForm.extend({
            events: {
                'change select': 'updateModel'
            },
            isGroup: true,
            label: __('oro_config.form.config.group.localization.title'),
            template: _.template(template),
            code: 'oro_config_localization',

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: this.label
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                FetcherRegistry.getFetcher('ui-locale').fetchAll().then(function (locales) {
                    this.$el.html(this.template({
                        locales: locales.reduce((result, locale) => {
                            result[locale.code] = locale.label;
                            return result;
                        }, {}),
                        selected: this.getFormData()['pim_ui___language'].value
                    }));

                    initSelect2.init(this.$('select'));
                    loadingMask.hide().$el.remove();
                }.bind(this));

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Update model after value change
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                var data = this.getFormData();
                data['pim_ui___language'].value = event.target.value;
                this.setData(data);
            }
        });
    }
);
