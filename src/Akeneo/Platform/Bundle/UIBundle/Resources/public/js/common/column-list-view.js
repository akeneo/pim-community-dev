'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'backbone',
    'pim/template/datagrid/configure-columns-action'
], function (
    $,
    _,
    __,
    Backbone,
    template
) {
    var Column = Backbone.Model.extend({
        defaults: {
            label: '',
            displayed: false,
            group: __('pim_datagrid.column_configurator.system_group')
        }
    });

    var ColumnList = Backbone.Collection.extend({ model: Column });

    return Backbone.View.extend({
        collection: ColumnList,

        template: _.template(template),

        events: {
            'input input[type="search"]':      'search',
            'click .nav-list li':              'filter',
            'click button.reset':              'reset',
            'click #column-selection .action': 'remove'
        },

        search: function (e) {
            var search = $(e.currentTarget).val();

            var matchesSearch = function (text) {
                return ('' + text).toUpperCase().indexOf(('' + search).toUpperCase()) >= 0;
            };

            this.$('#column-list').find('li').each(function () {
                if (matchesSearch($(this).data('value')) || matchesSearch($(this).text())) {
                    $(this).removeClass('AknVerticalList-item--hide');
                } else {
                    $(this).addClass('AknVerticalList-item--hide');
                }
            });
        },

        filter: function (e) {
            var filter = $(e.currentTarget).data('value');

            $(e.currentTarget).addClass('active').siblings('.active').removeClass('active');

            if (_.isUndefined(filter)) {
                this.$('#column-list li').removeClass('AknVerticalList-item--hide');
            } else {
                this.$('#column-list').find('li').each(function () {
                    if (filter === $(this).data('group')) {
                        $(this).removeClass('AknVerticalList-item--hide');
                    } else {
                        $(this).addClass('AknVerticalList-item--hide');
                    }
                });
            }
        },

        remove: function (e) {
            var $item = $(e.currentTarget).parent();
            $item.appendTo(this.$('#column-list'));

            var model = _.first(this.collection.where({code: $item.data('value')}));
            model.set('displayed', false);

            this.validateSubmission();
        },

        reset: function () {
            _.each(this.collection.where({displayed: true, removable: true}), function (model) {
                model.set('displayed', false);
                this.$('#column-selection li[data-value="' + model.get('code') + '"]').appendTo(this.$('#column-list'));
            }.bind(this));
            this.validateSubmission();
        },

        render: function () {
            var systemColumn = this.collection.where({group: __('pim_datagrid.column_configurator.system_group')});

            var groups = 0 !== systemColumn.length ?
                [{ position: -1, name: __('pim_datagrid.column_configurator.system_group'), itemCount: 0 }] :
                [];

            _.each(this.collection.toJSON(), function (column) {
                if (_.isEmpty(_.where(groups, {name: column.group}))) {
                    var position = parseInt(column.groupOrder, 10);
                    if (!_.isNumber(position) || !_.isEmpty(_.where(groups, {position: position}))) {
                        position = _.max(groups, function (group) {
                            return group.position;
                        }).position + 1;
                    }

                    groups.push({
                        position:  position,
                        name:      column.group,
                        itemCount: 1
                    });
                } else {
                    _.first(_.where(groups, {name: column.group})).itemCount += 1;
                }
            });

            groups = _.sortBy(groups, function (group) {
                return group.position;
            });

            this.$el.html(
                this.template({
                    attributeGroupsLabel: __('pim_enrich.entity.attribute_group.plural_label'),
                    groups:  groups,
                    columns: this.collection.toJSON()
                })
            );

            this.$('#column-list, #column-selection').sortable({
                connectWith: '.connected-sortable',
                containment: this.$el,
                tolerance: 'pointer',
                cursor: 'move',
                cancel: 'div.alert',
                receive: function (event, ui) {
                    var model = _.first(this.collection.where({code: ui.item.data('value')}));
                    model.set('displayed', ui.sender.is('#column-list') && model.get('removable'));

                    if (!model.get('removable')) {
                        $(ui.sender).sortable('cancel');
                    } else {
                        this.validateSubmission();
                    }
                }.bind(this)
            }).disableSelection();

            this.$('ul').css('height', $(window).height() * 0.7);

            return this;
        },

        validateSubmission: function () {
            if (this.collection.where({displayed: true}).length) {
                this.$('.alert').hide();
                this.$('.AknMessageBox--error').addClass('AknMessageBox--hide');
                this.$el.closest('.modal')
                    .find('.btn.ok:not(.btn-primary)')
                    .addClass('btn-primary')
                    .attr('disabled', false);
            } else {
                this.$('.alert').show();
                this.$('.AknMessageBox--error').removeClass('AknMessageBox--hide');
                this.$el.closest('.modal')
                    .find('.btn.ok.btn-primary')
                    .removeClass('btn-primary')
                    .attr('disabled', true);
            }
        },

        getDisplayed: function () {
            return _.map(this.$('#column-selection li'), function (el) {
                return $(el).data('value');
            });
        }
    });
});
