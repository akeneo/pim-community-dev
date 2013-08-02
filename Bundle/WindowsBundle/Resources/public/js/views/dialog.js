var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.DialogView = Backbone.View.extend({
    options: {
        type: 'dialog',
        actionsEl: '.widget-actions',
        dialogOptions: null,
        url: false,
        elementFirst: true
    },
    actions: null,
    firstRun: true,
    contentTop: null,

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
        options = options || {};
        options.dialogOptions = options.dialogOptions || {};
        options.dialogOptions.limitTo = options.dialogOptions.limitTo || '#container';

        this._initModel(options);

        this.dialogContent = this.$el;
        this._initEmbeddedForm();

        var runner = function(handlers) {
            return function() {
                for (var i = 0; i < handlers.length; i++) {
                    if (_.isFunction(handlers[i])) {
                        handlers[i]();
                    }
                }
            }
        };
        this.options.dialogOptions.close = runner([_.bind(this.closeHandler, this), this.options.dialogOptions.close]);
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

    _initEmbeddedForm: function() {
        this.hasAdoptedActions = this._getActionsElement().length > 0;
        if (this.hasAdoptedActions) {
            this.form = this._getActionsElement().closest('form');

            var formAction = this.form.attr('action');
            if (formAction.length > 0 && formAction[0] != '#') {
                this.options.url = formAction;
            }
        }
    },

    /**
     * Move form actions to dialog
     */
    adoptActions: function() {
        if (this.hasAdoptedActions) {
            var actions = this._getActionsElement();
            var self = this;
            actions.find('[type=submit]').each(function(idx, btn) {
                $(btn).click(function() {
                    self.form.submit();
                    return false;
                });
            });
            this.form.submit(function() {
                self.loadContent(self.form.serialize(), self.form.attr('method'));
                return false;
            });
            actions.find('[type=reset]').each(function(idx, btn) {
                $(btn).click(function() {
                    $(self.form).trigger('reset');
                    self.widget.dialog('close');
                });
            });
            actions.show();

            var container = this.widget.dialog('actionsContainer');
            container.empty();
            this._getActionsElement().appendTo(container);
            this.widget.dialog('showActionsContainer');
        }
    },

    /**
     * Handle dialog close
     */
    closeHandler: function() {
        this.model.destroy();
        this.dialogContent.remove();
        this._getActionsElement().remove();
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

    /**
     * Get form buttons
     *
     * @returns {(*|jQuery|HTMLElement)}
     * @private
     */
    _getActionsElement: function() {
        if (!this.actions) {
            this.actions = this.options.actionsEl;
            if (typeof this.actions == 'string') {
                this.actions = this.dialogContent.find(this.actions);
            }
        }
        return this.actions;
    },

    close: function() {
        this.widget.dialog('close');
    },

    getWidget: function() {
        return this.widget;
    },

    /**
     * Render dialog
     */
    render: function() {
        if (!_.isUndefined(Oro.Events)) {
            Oro.Events.trigger('dialog.open_request:start', this);
        }
        var loadAllowed = this.$el.html().length == 0 || !this.options.elementFirst || (this.options.elementFirst && !this.firstRun);
        if (loadAllowed && this.options.url !== false) {
            this.loadContent();
        } else {
            this.show();
        }
        this.firstRun = false;
    },

    /**
     * Load dialog content
     *
     * @param {Object|null} data
     * @param {String|null} method
     */
    loadContent: function(data, method) {
        var url = this.options.url;
        if (typeof url == 'undefined' || !url) {
            url = window.location.href;
        }
        if (this.firstRun || typeof method == 'undefined' || !method) {
            method = 'get';
        }
        var options = {
            url: url,
            type: method
        };
        if (typeof data != 'undefined') {
            options.data = data;
        }
        options.data = (typeof options.data != 'undefined' ? options.data + '&' : '')
            + '_widgetContainer=' + this.options.type;

        Backbone.$.ajax(options).done(_.bind(function(content) {
            try {
                this.actions = null;
                this.dialogContent = $('<div/>').html(content);
                this._initEmbeddedForm();
                this.show();
            } catch (error) {
                // Remove state with unrestorable content
                this.model.destroy();
            }
        }, this));
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
            this.widget = this.dialogContent.dialog(this.options.dialogOptions);
        } else {
            this.widget.html(this.dialogContent);
        }

        this.adoptActions();
        this.adjustHeight();

        // Allow children to close this dialog
        this.dialogContent.on('click', '.close-dialog-btn', _.bind(function () {
            this.close();
            return true;
        }, this));

        if (!_.isUndefined(Oro.Events)) {
            Oro.Events.trigger('dialog.open_request:complete', this);
        }
    },

    adjustHeight: function() {
        var content = this.widget.find('.scrollable-container');

        // first execute
        if (_.isNull(this.contentTop)) {
            content.css('overflow', 'auto');

            var parentEl = content.parent();
            var topPaddingOffset = parentEl.is(this.widget)?0:parentEl.position().top;
            this.contentTop = content.position().top + topPaddingOffset;
            var widgetHeight = this.widget.height();
            content.outerHeight(this.widget.height() - this.contentTop);
            if (widgetHeight != this.widget.height()) {
                // there is some unpredictable offset
                this.contentTop += this.widget.height() - this.contentTop - content.outerHeight();
                content.outerHeight(this.widget.height() - this.contentTop);
            }
            this.widget.on("dialogresize", _.bind(this.adjustHeight, this));

        }

        content.each(_.bind(function(i, el){
            var $el = $(el);
            $el.outerHeight(this.widget.height() - this.contentTop);
        },this));
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
