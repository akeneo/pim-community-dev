'use strict';

define(
    [
        'underscore',
        'pim/form',
        'pim/completeness-manager',
        'text!pim/template/product/panel/completeness',
        'pim/i18n'
    ],
    function(_, BaseForm, CompletenessManager, template, i18n) {
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
                                completenesses: completenesses,
                                i18n: i18n
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
