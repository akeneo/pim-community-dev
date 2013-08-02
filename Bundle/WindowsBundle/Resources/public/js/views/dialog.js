var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.DialogView = Oro.widget.Abstract.extend({
    options: _.extend(
        {
            type: 'dialog',
            dialogOptions: null
        },
        Oro.widget.Abstract.prototype.options
    ),

    // Windows manager global variables
    windowsPerRow: 10,
    windowOffsetX: 15,
    windowOffsetY: 15,
    windowX: 0,
    windowY: 0,
    defaultPos: 'center center',
    openedWindows: 0,

    /**
     * Initialize dialog
     */
    initialize: function(options) {
        options = options || {}
        this.initializeWidget(options);

        this.on('adoptedFormResetClick', _.bind(function() {
            this.widget.dialog('close')
        }, this));

        this.options.dialogOptions = this.options.dialogOptions || {};
        this.options.dialogOptions.title = this.options.dialogOptions.title || this.options.title;
        this.options.dialogOptions.limitTo = this.options.dialogOptions.limitTo || '#container';

        this._initModel(this.options);
        this.widgetContent = this.$el;

        var runner = function(handlers) {
            return function() {
                for (var i = 0; i < handlers.length; i++) {
                    if (_.isFunction(handlers[i])) {
                        handlers[i]();
                    }
                }
            }
        };

        var closeHandlers = [_.bind(this.closeHandler, this)];
        if (this.options.dialogOptions.close !== undefined) {
            closeHandlers.push(this.options.dialogOptions.close);
        }

        this.options.dialogOptions.close = runner(closeHandlers);

        this.on('contentLoadError', _.bind(this.loadErrorHandler, this));
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
            this.model = new Oro.widget.StateModel();
        }
    },

    /**
     * Handle dialog close
     */
    closeHandler: function() {
        this.model.destroy({
            error: _.bind(function(model, xhr, options) {
                // Suppress error if it's 404 response and not debug mode
                if (xhr.status != 404 || Oro.debug) {
                    Oro.BackboneError.Dispatch(model, xhr, options);
                }
            }, this)
        });
        this.widgetContent.remove();
        this._getActionsElement().remove();
        this.widget.remove();
    },

    handleStateChange: function(e, data) {
        if (this.restoreMode) {
            this.restoreMode = false;
            return;
        }
        var saveData = _.omit(this.options, ['dialogOptions', 'el', 'model']);
        if (!saveData.url) {
            saveData.el = $('<div/>').append(this.$el).html();
        }
        saveData.dialogOptions = {};
        _.each(this.options.dialogOptions, function(val, key) {
            if (!_.isFunction(val) && key != 'position') {
                saveData.dialogOptions[key] = val;
            }
        }, this);

        saveData.dialogOptions.title = $(e.target).dialog('option', 'title');
        saveData.dialogOptions.state = data.state;
        saveData.dialogOptions.snapshot = data.snapshot;

        this.model.save({data: saveData});
    },

    close: function() {
        this.widget.dialog('close');
    },

    getWidget: function() {
        return this.widget;
    },

    loadErrorHandler: function()
    {
        this.model.destroy();
    },

    renderActions: function() {
        var container = this.widget.dialog('actionsContainer');
        container.empty();
        this.getPreparedActions().appendTo(container);
        this.widget.dialog('showActionsContainer');
    },

    /**
     * Show dialog
     */
    show: function() {
        if (!this.widget) {
            if (typeof this.options.dialogOptions.position == 'undefined') {
                this.options.dialogOptions.position = this._getWindowPlacement();
            }
            this.options.dialogOptions.stateChange = _.bind(this.handleStateChange, this);
            this.widget = this.widgetContent.dialog(this.options.dialogOptions);
        } else {
            this.widget.html(this.widgetContent);
        }
        Oro.widget.Abstract.prototype.show.apply(this);
    },

    /**
     * Get next window position based
     *
     * @returns {{my: string, at: string, of: (*|jQuery|HTMLElement), within: (*|jQuery|HTMLElement)}}
     * @private
     */
    _getWindowPlacement: function() {
        var offset = 'center+' + Oro.widget.DialogView.prototype.windowX + ' center+' + Oro.widget.DialogView.prototype.windowY;

        Oro.widget.DialogView.prototype.openedWindows++;
        if (Oro.widget.DialogView.prototype.openedWindows % Oro.widget.DialogView.prototype.windowsPerRow === 0) {
            var rowNum = Oro.widget.DialogView.prototype.openedWindows / Oro.widget.DialogView.prototype.windowsPerRow;
            Oro.widget.DialogView.prototype.windowX = rowNum * Oro.widget.DialogView.prototype.windowsPerRow * Oro.widget.DialogView.prototype.windowOffsetX;
            Oro.widget.DialogView.prototype.windowY = 0;

        } else {
            Oro.widget.DialogView.prototype.windowX += Oro.widget.DialogView.prototype.windowOffsetX;
            Oro.widget.DialogView.prototype.windowY += Oro.widget.DialogView.prototype.windowOffsetY;
        }

        return {
            my: offset,
            at: Oro.widget.DialogView.prototype.defaultPos
        };
    }
});

Oro.widget.Manager.registerWidgetContainer('dialog', Oro.widget.DialogView);
