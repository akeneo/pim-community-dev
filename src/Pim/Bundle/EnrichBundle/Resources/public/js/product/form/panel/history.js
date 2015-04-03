'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/panel/history',
        'routing',
        'oro/mediator',
        'backbone/bootstrap-modal'
    ],
    function(_, Backbone, BaseForm, template, Routing, mediator) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane history-panel',
            code: 'history',
            versions: [],
            actions: {},
            events: {
                'click .expand-history':   'expandHistory',
                'click .collapse-history': 'collapseHistory',
                'click .expanded tbody tr:not(.changeset)': 'toggleVersion'
            },
            initialize: function() {
                this.actions = {};

                mediator.on('post_save', _.bind(this.updateHistory, this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addPanel('history', 'History');

                mediator.on('post_save', _.bind(this.updateHistory, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (0 === this.versions.length) {
                    this.updateHistory();

                    return this;
                }

                if (this.getRoot().model.get('meta')) {

                    this.$el.html(
                        this.template({
                            versions: this.versions.slice(0, 10),
                            expanded: this.getParent().getParent().state.get('fullPanel'),
                            hasAction: this.actions
                        })
                    );

                    mediator.trigger('history:rendered:before');
                    if (this.getParent().getParent().state.get('fullPanel') && this.actions) {
                        _.each(this.$el.find('td.actions'), _.bind(function(element) {
                            _.each(this.actions, _.bind(function(action) {
                                $(element).append(action.clone(true));
                            }, this));
                        }, this));
                    }
                    mediator.trigger('history:rendered:after');

                    this.delegateEvents();
                    this.renderExtensions();
                }

                return this;
            },
            updateHistory: function () {
                if (this.getRoot().model.get('meta')) {
                    $.get(
                        Routing.generate(
                            'pim_enrich_product_history_rest_get',
                            {
                                entityId: this.getData().meta.id
                            }
                        )
                    ).done(_.bind(function(versions) {
                        this.versions = versions;
                        this.render();
                    }, this));
                }
            },
            addAction: function(code, element) {
                this.actions[code] = element;
            },
            expandHistory: function() {
                this.getParent().openFullPanel();
                this.render();
            },
            collapseHistory: function() {
                this.getParent().closeFullPanel();
                this.render();
            },
            toggleVersion: function (event) {
                var $row = $(event.currentTarget);
                var $body = $row.parent();
                $body.find('tr.changeset').addClass('hide');
                $body.find('i.icon-chevron-down').toggleClass('icon-chevron-right icon-chevron-down');

                if (!$row.hasClass('expanded')) {
                    $row.next('tr.changeset').removeClass('hide');
                    $row.find('i').toggleClass('icon-chevron-right icon-chevron-down');
                }
                $row.siblings().removeClass('expanded');
                $row.toggleClass('expanded');
            }
        });
    }
);
