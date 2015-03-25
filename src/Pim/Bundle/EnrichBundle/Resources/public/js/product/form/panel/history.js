'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/panel/history',
        'text!pim/template/product/panel/history-modal',
        'routing',
        'oro/mediator',
        'backbone/bootstrap-modal'
    ],
    function(_, Backbone, BaseForm, template, modalTemplate, Routing, mediator) {
        return BaseForm.extend({
            template: _.template(template),
            modalTemplate: _.template(modalTemplate),
            className: 'panel-pane',
            code: 'history',
            versions: [],
            events: {
                'click #expand-history': 'showHistoryModal'
            },
            configure: function () {
                this.getRoot().addPanel('history', 'History');

                mediator.on('post_save', _.bind(this.update, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (this.getRoot().model.get('meta')) {
                    this.loadData().done(_.bind(function(data) {
                        this.versions = data;

                        this.$el.html(
                            this.template({
                                versions: this.versions.slice(0, 10)
                            })
                        );
                        this.delegateEvents();
                    }, this));
                }

                return this;
            },
            update: function() {
                this.render();
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
            },
            showHistoryModal: function () {
                var modal = new Backbone.BootstrapModal({
                    className: 'modal modal-large history-modal',
                    modalOptions: {
                        backdrop: 'static',
                        keyboard: false
                    },
                    allowCancel: false,
                    okCloses: true,
                    cancelText: 'Close',
                    title: 'Product history',
                    content: this.modalTemplate({ versions: this.versions }),
                    okText: 'Close'
                });

                modal.open();

                modal.$el.on('click', 'tbody tr:not(.changeset)', function (event) {
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
                });
            }
        });
    }
);
