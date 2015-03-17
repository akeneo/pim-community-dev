'use strict';

define(
    [
        'underscore',
        'pim/form',
        'pim/completeness-manager',
        'text!pim/template/product/panel/completeness'
    ],
    function(_, BaseForm, CompletenessManager, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane',
            code: 'completeness',
            configure: function () {
                this.getRoot().addPanel('completeness', 'Completeness');

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (this.getRoot().model.get('meta')) {
                    CompletenessManager.getCompleteness(this.getRoot().model.get('meta').id).done(_.bind(function(completenesses) {
                        console.log(completenesses);
                        this.$el.html(
                            this.template({
                                state: this.getRoot().state.toJSON(),
                                completenesses: completenesses
                            })
                        );
                    }, this));
                }

                return this;
            }
        });
    }
);
