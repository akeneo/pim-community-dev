/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/messenger', 'routing', 'oro/loading-mask',
    'oro/query-designer/column/view'],
function(_, Backbone, __, app, messenger, routing, LoadingMask,
         ColumnView) {
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
            entityName: null,
            storageElementSelector: null,
            loadColumnsUrl: null,
            columnsOptions: {
                collection: null,
                itemTemplateSelector: null,
                itemFormSelector: null
            },
            filtersOptions: {
                collection: null
            }
        },

        /** @property {oro.LoadingMask} */
        loadingMask: null,

        /** @property {oro.queryDesigner.column.View} */
        columnsView: null,

        /** @property {jQuery} */
        storageEl: null,

        initialize: function() {
            this.options.loadColumnsUrl = this.options.loadColumnsUrl || function (entityName) {
                return routing.generate('oro_api_get_entity_fields', {
                    'entityName': entityName,
                    'with-relations': true,
                    'with-entity-details': true
                })
            };
        },

        isEmpty: function () {
            return this.columnsView.getCollection().isEmpty();
        },

        changeEntity: function (entityName) {
            this.disableViews();
            $.ajax({
                url: this.options.loadColumnsUrl(entityName.replace(/\\/g,"_")),
                success: _.bind(function(data) {
                    this.updateColumnSelectors(entityName, data);
                    this.enableViews();
                }, this),
                error: _.bind(function (jqXHR) {
                    this.showError(jqXHR.responseJSON);
                    this.enableViews();
                }, this)
            });
        },

        updateColumnStorage: function () {
            if (this.storageEl) {
                var columns = this.columnsView.getCollection().toJSON();
                _.each(columns, function (value) {
                    delete value.id;
                });
                var data = {
                    columns: columns
                };
                this.storageEl.val(JSON.stringify(data));
            }
        },

        render: function() {
            if (this.options.storageElementSelector) {
                this.storageEl = $(this.options.storageElementSelector);
            }

            // initialize loading mask control
            this.loadingMask = new LoadingMask();
            this.$el.append(this.loadingMask.render().$el);

            var data = [];
            if (this.storageEl && this.storageEl.val() != '') {
                var data = JSON.parse(this.storageEl.val());
            }

            // initialize columns view
            var columnsOptions = _.extend(this.options.columnsOptions, {entityName: this.options.entityName});
            this.columnsView = new ColumnView(columnsOptions);
            this.columnsView.render();
            delete this.options.columnsOptions;
            if (!_.isUndefined(data['columns']) && !_.isEmpty(data['columns'])) {
                this.columnsView.getCollection().reset(data['columns']);
            }
            this.listenTo(this.columnsView, 'collection:change', _.bind(this.updateColumnStorage, this));

            return this;
        },

        enableViews: function () {
            this.loadingMask.hide();
        },

        disableViews: function () {
            this.loadingMask.show();
        },

        updateColumnSelectors: function (entityName, entityFields) {
            this.options.entityName = entityName;
            this.columnsView.changeEntity(entityName);
            this.columnsView.updateColumnSelector(entityFields);
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
