"use strict";

define([
        'pim/form',
        'pim/fetcher-registry',
        'oro/loading-mask',
        'text!oro/template/system/group/localization'
    ],
    function(
        BaseForm,
        FetcherRegistry,
        LoadingMask,
        template
    ) {
        return BaseForm.extend({
            className: 'tab-pane',
            events: {
                'change select': 'updateModel'
            },
            isGroup: true,
            label: _.__('oro_config.form.config.group.localization.title'),
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                FetcherRegistry.getFetcher('ui-locale').fetchAll().then(function (locales) {
                    this.$el.html(this.template({
                        locales: locales,
                        selected: this.getFormData()['oro_locale___language'].value
                    }));

                    this.$('select').select2();
                    loadingMask.hide().$el.remove();
                }.bind(this));

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Update model after value change
             *
             * @param {Event}
             */
            updateModel: function (event) {
                var data = this.getFormData();
                data['oro_locale___language'].value = event.target.value;
                this.setData(data);
            }
        });
    }
);
