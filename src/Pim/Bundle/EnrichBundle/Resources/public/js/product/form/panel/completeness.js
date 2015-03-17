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
            events: {
                'click header': 'switchChannel'
            },
            configure: function () {
                this.getRoot().addPanel('completeness', 'Completeness');

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (this.getRoot().model.get('meta')) {
                    CompletenessManager.getCompleteness(this.getRoot().model.get('meta').id).done(_.bind(function(completenesses) {
                        this.$el.html(
                            this.template({
                                state: this.getRoot().state.toJSON(),
                                completenesses: completenesses
                            })
                        );
                        this.delegateEvents();
                    }, this));
                }

                return this;
            },
            switchChannel: function(event) {
                var $completenessBlock = $(event.currentTarget).parents('.completeness-block');
                if ($completenessBlock.attr('data-closed') === 'false') {
                    $completenessBlock.attr('data-closed', 'true');
                } else {
                    $completenessBlock.attr('data-closed', 'false');
                }
            }
        });
    }
);
