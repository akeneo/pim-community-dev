var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.Abstract = Backbone.View.extend({
    options: {
        type: 'widget',
        actionsEl: '.widget-actions',
        url: false,
        elementFirst: true,
        title: '',
        wid: null
    },

    setTitle: function(title) {
        console.warn('Implement setTitle');
    },

    getActionsElement: function() {
        console.warn('Implement getActionsElement');
    },

    /**
     * Initialize
     */
    initializeWidget: function(options) {
        if (this.options.wid) {
            this._wid = this.options.wid;
        }

        this.on('adoptedFormSubmitClick', _.bind(this._onAdoptedFormSubmitClick, this));
        this.on('adoptedFormResetClick', _.bind(this._onAdoptedFormResetClick, this));
        this.on('adoptedFormSubmit', _.bind(this._onAdoptedFormSubmit, this));

        this.actions = [];
        this.firstRun = true;
    },

    getWid: function() {
        if (!this._wid) {
            this._wid = this._getUniqueIdentifier();
        }
        return this._wid;
    },

    _getUniqueIdentifier: function() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
    },

    /**
     * Move form actions to widget actions
     */
    _adoptFormActions: function() {
        this._initEmbeddedForm();
        if (this.hasAdoptedActions && this.form !== undefined) {
            var actions = this._getAdoptedActionsContainer().find('button, input, a');
            var self = this;
            _.each(actions, function(action) {
                if (action.type.toLowerCase() == 'submit') {
                    $(action).click(function() {
                        self.trigger('adoptedFormSubmitClick', self.form, self);
                        return false;
                    });
                }
                if (action.type.toLowerCase() == 'reset') {
                    $(action).click(function() {
                        self.trigger('adoptedFormResetClick', self.form, self);
                    });
                }
                self.actions.push(action);
            });
            this.form.submit(function(e) {
                e.stopImmediatePropagation();
                self.trigger('adoptedFormSubmit', self.form, self);
                return false;
            });
            this._getAdoptedActionsContainer().remove();
        }
    },

    _initEmbeddedForm: function() {
        var adoptedActions = this._getAdoptedActionsContainer();
        this.hasAdoptedActions = adoptedActions.length > 0;
        if (this.hasAdoptedActions) {
            var form = adoptedActions.closest('form');

            if (form.length > 0) {
                this.form = form;
                var formAction = this.form.attr('action');
                if (formAction.length > 0 && formAction[0] != '#') {
                    this.options.url = formAction;
                }
            }
        }
    },

    _getAdoptedActionsContainer: function() {
        if (this.options.actionsEl !== undefined) {
            if (typeof this.options.actionsEl == 'string') {
                return this.widgetContent.find(this.options.actionsEl);
            } else if (_.isElement(this.options.actionsEl )) {
                return this.options.actionsEl;
            }
        }
        return false;
    },

    _onAdoptedFormSubmitClick: function(form)
    {
        form.submit();
    },

    _onAdoptedFormSubmit: function(form)
    {
        this.loadContent(form.serialize(), form.attr('method'));
    },

    _onAdoptedFormResetClick: function(form)
    {
        $(form).trigger('reset');
    },

    addAction: function(key, actionElement) {
        if (!this.hasAction(key)) {
            this.actions[key] = actionElement;
            this.getActionsElement().append(actionElement);
        }
    },

    getActions: function() {
        return this.actions;
    },

    removeAction: function(key) {
        if (this.hasAction(key)) {
            if (_.isElement(this.actions[key])) {
                this.actions[key].remove();
            }
            delete this.actions[key];
        }
    },

    hasAction: function(key) {
        return this.actions.hasOwnProperty(key);
    },

    _renderActions: function() {
        var container = this.getActionsElement();
        container.empty();

        this._adoptFormActions();
        for (var actionKey in this.actions) if (this.actions.hasOwnProperty(actionKey)) {
            container.append(this.actions[actionKey]);
        }
    },

    /**
     * Render widget
     */
    render: function() {
        var loadAllowed = this.$el.html().length == 0 || !this.options.elementFirst
            || (this.options.elementFirst && !this.firstRun);
        if (loadAllowed && this.options.url !== false) {
            this.loadContent();
        } else {
            this.show();
            this.trigger('render', this.$el, this);
        }
        this.firstRun = false;
    },

    /**
     * Load content
     *
     * @param {Object|null} data
     * @param {String|null} method
     */
    loadContent: function(data, method) {
        var url = this.options.url;
        if (url === undefined || !url) {
            url = window.location.href;
        }
        if (this.firstRun || method === undefined || !method) {
            method = 'get';
        }
        var options = {
            url: url,
            type: method
        };
        if (data !== undefined) {
            options.data = data;
        }
        options.data = (options.data !== undefined ? options.data + '&' : '')
            + '_widgetContainer=' + this.options.type + '&_wid=' + this.getWid();

        Backbone.$.ajax(options).done(_.bind(function(content) {
            try {
                this.trigger('contentLoad', content, this);
                this.actionsEl = null;
                this.widgetContent = $('<div/>').html(content);
                this.show();
                this.trigger('render', this.$el, this);
            } catch (error) {
                // Remove state with unrestorable content
                this.trigger('contentLoadError', this);
            }
        }, this));
    },

    show: function() {
        this.$el.attr('data-wid', this.getWid());
        this._renderActions();
        this.$el.trigger('widgetize', this);
        this.trigger('widgetRender', this.widgetContent, this);
    }
});
