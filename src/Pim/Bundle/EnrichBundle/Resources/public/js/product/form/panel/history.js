'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/panel/history',
        'routing',
    ],
    function(_, BaseForm, template, Routing) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane',
            code: 'history',
            versions: [],
            configure: function () {
                this.getRoot().addPanel('history', 'History');

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (this.getRoot().model.get('meta')) {
                    this.loadData().done(_.bind(function(data) {
                        this.versions = data;

                        this.$el.html(
                            this.template({
                                versions: this.versions
                            })
                        );
                    }, this));
                }

                return this;
            },
            loadData: function () {
                return $.get(
                    Routing.generate(
                        'pim_enrich_product_history_rest_get',
                        {
                            entityId: this.getData().meta.id
                        }
                    )
                );
            }
        });
    }
);
