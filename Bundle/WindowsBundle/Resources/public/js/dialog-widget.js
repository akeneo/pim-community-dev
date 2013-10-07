/* global define */
define(['underscore', 'backbone', 'oro/app', 'oro/error', 'oro/abstract-widget', 'oro/dialog/state/model',
    'jquery.dialog.extended'],
function(_, Backbone, app, error, AbstractWidget, StateModel) {
    'use strict';

    /**
     * @export  oro/dialog-widget
     * @class   oro.DialogWidget
     * @extends oro.AbstractWidget
     */
    var DialogWidget = AbstractWidget.extend({
        options: _.extend({}, AbstractWidget.prototype.options, {
            type: 'dialog',
            dialogOptions: null,
            stateEnabled: true,
            incrementalPosition: true
        }),

        // Windows manager global variables
        windowsPerRow: 10,
        windowOffsetX: 15,
        windowOffsetY: 15,
        windowX: 0,
        windowY: 0,
        defaultPos: 'center center',
        openedWindows: 0,
        contentTop: null,

        /**
         * Initialize dialog
         */
        initialize: function(options) {
            options = options || {};

            this.on('adoptedFormResetClick', _.bind(this.remove, this));

            this.options.dialogOptions = this.options.dialogOptions || {};
            this.options.dialogOptions.title = this.options.dialogOptions.title || this.options.title;
            this.options.dialogOptions.limitTo = this.options.dialogOptions.limitTo || '#container';

            if (this.options.stateEnabled) {
                this._initModel(this.options);
            }

            var runner = function(handlers) {
                return function() {
                    for (var i = 0; i < handlers.length; i++) {
                        if (_.isFunction(handlers[i])) {
                            handlers[i]();
                        }
                    }
                };
            };

            var closeHandlers = [_.bind(this.closeHandler, this)];
            if (this.options.dialogOptions.close !== undefined) {
                closeHandlers.push(this.options.dialogOptions.close);
            }

            this.options.dialogOptions.close = runner(closeHandlers);

            this.on('widgetRender', _.bind(this._initAdjustHeight, this));
            this.on('contentLoadError', _.bind(this.loadErrorHandler, this));

            this.initializeWidget(options);
        },

        setTitle: function(title) {
            this.widget.dialog("option", "title", title);
        },

        _initModel: function(options) {
            if (this.model) {
                this.restoreMode = true;
                var attributes = this.model.get('data');
                _.extend(options, attributes);
                if (_.isObject(attributes.dialogOptions)) {
                    options.dialogOptions = _.extend(options.dialogOptions, attributes.dialogOptions);
                }
                this.options = options;
                if (this.options.el) {
                    this.setElement(this.options.el);
                } else if (this.model.get('id')) {
                    var restoredEl = Backbone.$('#widget-restored-state-' + this.model.get('id'));
                    if (restoredEl.length) {
                        this.setElement(restoredEl);
                    }
                }
            } else {
                this.model = new StateModel();
            }
        },

        /**
         * Handle dialog close
         */
        closeHandler: function() {
            if (this.model) {
                this.model.destroy({
                    error: _.bind(function(model, xhr, options) {
                        // Suppress error if it's 404 response and not debug mode
                        if (xhr.status != 404 || app.debug) {
                            error.dispatch(model, xhr, options);
                        }
                    }, this)
                });
            }
            this._hideLoading();
            this.widget.remove();
            AbstractWidget.prototype.remove.call(this);
        },

        handleStateChange: function(e, data) {
            if (!this.options.stateEnabled) {
                return;
            }
            if (this.restoreMode) {
                this.restoreMode = false;
                return;
            }
            var saveData = _.omit(this.options, ['dialogOptions', 'el', 'model']);
            if (!saveData.url) {
                saveData.el = Backbone.$('<div/>').append(this.$el.clone()).html();
            }
            saveData.dialogOptions = {};
            _.each(this.options.dialogOptions, function(val, key) {
                if (!_.isFunction(val) && key !== 'position') {
                    saveData.dialogOptions[key] = val;
                }
            }, this);

            saveData.dialogOptions.title = Backbone.$(e.target).dialog('option', 'title');
            saveData.dialogOptions.state = data.state;
            saveData.dialogOptions.snapshot = data.snapshot;

            if (this.model) {
                this.model.save({data: saveData});
            }
        },

        remove: function() {
            // Close will trigger call of closeHandler where Backbone.View.remove will be called
            this.widget.dialog('close');
        },

        getWidget: function() {
            return this.widget;
        },

        loadErrorHandler: function()
        {
            if (this.model) {
                this.model.destroy();
            }
        },

        getActionsElement: function() {
            if (!this.actionsEl) {
                this.actionsEl = Backbone.$('<div class="pull-right"/>').appendTo(
                    Backbone.$('<div class="form-actions widget-actions"/>').appendTo(
                        this.widget.dialog('actionsContainer')
                    )
                );
            }
            return this.actionsEl;
        },

        _clearActionsContainer: function() {
            this.widget.dialog('actionsContainer').empty();
        },

        _renderActions: function() {
            AbstractWidget.prototype._renderActions.apply(this);
            this.widget.dialog('showActionsContainer');
        },

        /**
         * Show dialog
         */
        show: function() {
            if (!this.widget) {
                if (typeof this.options.dialogOptions.position === 'undefined') {
                    this.options.dialogOptions.position = this._getWindowPlacement();
                }
                this.options.dialogOptions.stateChange = _.bind(this.handleStateChange, this);
                this.widget = Backbone.$('<div/>').append(this.$el).dialog(this.options.dialogOptions);
            } else {
                this.widget.html(this.$el);
            }
            this.loadingElement = this.$el.closest('.ui-dialog');
            AbstractWidget.prototype.show.apply(this);
        },

        _initAdjustHeight: function(content) {
            this.widget.off("dialogresize dialogmaximize dialogrestore", _.bind(this._fixScrollableHeight, this));
            var scrollableContent = content.find('.scrollable-container');
            if (scrollableContent.length) {
                scrollableContent.css('overflow', 'auto');
                this.widget.on("dialogresize dialogmaximize dialogrestore", _.bind(this._fixScrollableHeight, this));
                this._fixScrollableHeight();
            }
        },

        _fixScrollableHeight: function() {
            var widget = this.widget;
            widget.find('.scrollable-container').each(_.bind(function(i, el){
                var $el = $(el);
                var height = widget.height() - $el.position().top;
                if (height) {
                    $el.height(height);
                }
            },this));
        },

        /**
         * Get next window position based
         *
         * @returns {{my: string, at: string, of: (*|jQuery|HTMLElement), within: (*|jQuery|HTMLElement)}}
         * @private
         */
        _getWindowPlacement: function() {
            if (!this.options.incrementalPosition) {
                return {
                    my: 'center center',
                    at: DialogWidget.prototype.defaultPos
                };
            }
            var offset = 'center+' + DialogWidget.prototype.windowX + ' center+' + DialogWidget.prototype.windowY;

            DialogWidget.prototype.openedWindows++;
            if (DialogWidget.prototype.openedWindows % DialogWidget.prototype.windowsPerRow === 0) {
                var rowNum = DialogWidget.prototype.openedWindows / DialogWidget.prototype.windowsPerRow;
                DialogWidget.prototype.windowX = rowNum * DialogWidget.prototype.windowsPerRow * DialogWidget.prototype.windowOffsetX;
                DialogWidget.prototype.windowY = 0;

            } else {
                DialogWidget.prototype.windowX += DialogWidget.prototype.windowOffsetX;
                DialogWidget.prototype.windowY += DialogWidget.prototype.windowOffsetY;
            }

            return {
                my: offset,
                at: DialogWidget.prototype.defaultPos
            };
        }
    });

    return DialogWidget;
});
