'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/completeness-manager',
        'text!pim/template/product/panel/completeness',
        'pim/i18n',
        'oro/mediator'
    ],
    function ($, _, BaseForm, CompletenessManager, template, i18n, mediator) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane',
            code: 'completeness',
            events: {
                'click header': 'switchChannel',
                'click .missing-attributes span': 'showAttribute'
            },
            configure: function () {
                this.getRoot().addPanel('completeness', 'Completeness');

                mediator.on('post_save', _.bind(this.update, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (this.getRoot().model.get('meta')) {
                    CompletenessManager.getCompleteness(this.getRoot().model.get('meta').id)
                        .done(_.bind(function (completenesses) {
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
            switchChannel: function (event) {
                var $completenessBlock = $(event.currentTarget).parents('.completeness-block');
                if ($completenessBlock.attr('data-closed') === 'false') {
                    $completenessBlock.attr('data-closed', 'true');
                } else {
                    $completenessBlock.attr('data-closed', 'false');
                }
            },
            showAttribute: function (event) {
                mediator.trigger(
                    'show_attribute',
                    {
                        attribute: event.currentTarget.dataset.attribute,
                        locale: event.currentTarget.dataset.locale,
                        scope: event.currentTarget.dataset.channel
                    }
                );
            },
            update: function () {
                if (this.getRoot().model.get('meta')) {
                    CompletenessManager.invalidateCache(this.getRoot().model.get('meta').id);
                }

                this.render();
            }
        });
    }
);
