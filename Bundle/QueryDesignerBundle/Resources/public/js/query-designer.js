/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/messenger', 'routing',
    'oro/query-designer/column/collection', 'oro/query-designer/column/model', 'oro/query-designer/column/view'],
function(_, Backbone, __, app, messenger, routing,
         ColumnCollection, ColumnModel, ColumnView) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/query-designer
     * @class   oro.QueryDesigner
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        options: {
            entity: null,
            storageElementSelector: null,
            columnsOptions: {
                collection: null,
                itemTemplateSelector: null,
                itemFormSelector: null
            },
            filtersOptions: {
                collection: null
            }
        },

        /** @property */
        columnsView: ColumnView,

        /** @property jQuery */
        storageEl: null,

        initialize: function() {
        },

        hasData: function () {
            return !this.columnsView.getCollection().isEmpty();
        },

        changeEntity: function (entity) {
            this.disableColumnSelectors();
            $.ajax({
                url: routing.generate('oro_api_get_entity_fields', {'entityName': entity.replace(/\\/g,"_")}),
                data: {
                    'with-relations': true
                },
                success: _.bind(function(data) {
                    this.options.entity = entity;
                    this.columnsView.getCollection().reset();
                    this.updateColumnSelectors(data);
                }, this),
                error: _.bind(function (jqXHR) {
                    this.showError(jqXHR.responseJSON);
                    this.enableColumnSelectors();
                }, this)
            });
        },

        updateColumnStorage: function (columns) {
            var data = {
                columns: columns
            };
            this.storageEl.val(JSON.stringify(data));
        },

        render: function() {
            // prepare options for child views
            if (_.isNull(this.options.storageElementSelector)) {
                _.extend(this.options.columnsOptions, {
                    updateStorage: _.bind(this.updateColumnStorage, this)
                });
            } else {
                this.storageEl = $(this.options.storageElementSelector);
            }

            // initialize columns view
            this.columnsView = new this.columnsView(this.options.columnsOptions);
            this.columnsView.render();

            return this;
        },

        enableColumnSelectors: function () {
            this.columnsView.getColumnSelector().attr('disabled', false);
        },

        disableColumnSelectors: function () {
            this.columnsView.getColumnSelector().attr('disabled', true);
        },

        updateColumnSelectors: function (data) {
            this.columnsView.updateColumnSelector(data);
        },

        showError: function (err) {
            if (!_.isUndefined(console)) {
                console.error(_.isUndefined(err.stack) ? err : err.stack);
            }
            var msg = __('Sorry, unexpected error was occurred');
            if (app.debug) {
                if (!_.isUndefined(err.message)) {
                    msg += ': ' + err.message;
                } else if (!_.isUndefined(err.errors) && _.isArray(err.errors)) {
                    msg += ': ' + err.errors.join();
                } else if (_.isString(err)) {
                    msg += ': ' + err;
                }
            }
            messenger.notificationFlashMessage('error', msg);
        }
    });
});
