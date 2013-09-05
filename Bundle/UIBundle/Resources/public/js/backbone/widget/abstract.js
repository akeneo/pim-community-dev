var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.Abstract = Backbone.View.extend({
    options: {
        type: 'widget',
        actionsEl: '.widget-actions',
        url: false,
        elementFirst: true,
        title: '',
        alias: null,
        wid: null
    },

    initialize: function(options) {
        options = options || {};
        this.initializeWidget(options);
    },

    setTitle: function(title) {
        console.warn('Implement setTitle');
    },

    getActionsElement: function() {
        console.warn('Implement getActionsElement');
    },

    remove: function() {
        Oro.widget.Manager.removeWidget(this.getWid());
        Backbone.View.prototype.remove.call(this);
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

        this.actions = {};
        this.firstRun = true;
    },

    getWid: function() {
        if (!this._wid) {
            this._wid = this._getUniqueIdentifier();
        }
        return this._wid;
    },

    getAlias: function() {
        return this.options.alias;
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
    _adoptWidgetActions: function() {
        var adoptedActionsContainer = this._getAdoptedActionsContainer();
        if (adoptedActionsContainer.length > 0) {
            var self = this;
            var form = adoptedActionsContainer.closest('form');
            var actions = adoptedActionsContainer.find('button, input, a');

            if (form.length > 0) {
                this.form = form;
                var formAction = this.form.attr('action');
                if (formAction.length > 0 && formAction[0] != '#') {
                    this.options.url = formAction;
                }
                this.form.submit(function(e) {
                    e.stopImmediatePropagation();
                    self.trigger('adoptedFormSubmit', self.form, self);
                    return false;
                });
            }

            self.actions['adopted'] = {};
            _.each(actions, function(action, idx) {
                var $action = $(action);
                var actionId = $action.data('action-name') ? $action.data('action-name') : 'adopted_action_' + idx;
                switch (action.type.toLowerCase()) {
                    case 'submit':
                        actionId = 'form_submit';
                        break;
                    case 'reset':
                        actionId = 'form_reset';
                        break;
                }
                self.actions['adopted'][actionId] = $action;
            });
            adoptedActionsContainer.remove();
        }
    },

    _getAdoptedActionsContainer: function() {
        if (this.options.actionsEl !== undefined) {
            if (typeof this.options.actionsEl == 'string') {
                return this.$el.find(this.options.actionsEl);
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

    addAction: function(key, actionElement, section) {
        if (section === undefined) {
            section = 'main';
        }
        if (!this.hasAction(key, section)) {
            this.actions[key] = actionElement;
            this.getActionsElement().append(actionElement);
        }
    },

    getActions: function() {
        return this.actions;
    },

    setUrl: function(url) {
        this.options.url = url;
    },

    removeAction: function(key, section) {
        var self = this;
        var remove = function(actions, key) {
            if (_.isElement(self.actions[key])) {
                self.actions[key].remove();
            }
            delete self.actions[key];
        };
        if (this.hasAction(key, section)) {
            if (section !== undefined) {
                remove(this.actions[section], key);
            } else {
                _.each(this.actions, function(actions, section) {
                    if (self.hasAction(key, section)) {
                        remove(actions, key);
                    }
                });
            }
        }
    },

    hasAction: function(key, section) {
        if (section !== undefined) {
            return this.actions[section].hasOwnProperty(key);
        } else {
            var hasAction = false;
            _.each(this.actions, function(actions) {
                if (actions.hasOwnProperty(key)) {
                    hasAction = true;
                }
            });
            return hasAction;
        }
    },

    getAction: function(key, section) {
        var action = null;
        if (this.hasAction(key, section)) {
            if (section !== undefined) {
                action = this.actions[section][key];
            } else {
                _.each(this.actions, function(actions) {
                    if (actions.hasOwnProperty(key)) {
                        action = actions[key];
                    }
                });
            }
        }
        return action;
    },

    _renderActions: function() {
        var self = this;
        this._clearActionsContainer();
        var container = this.getActionsElement();

        _.each(this.actions, function(actions, section) {
            var sectionContainer = $('<div id="' + section + '"/>');
            _.each(actions, function(action) {
                self._initActionEvents(action);
                sectionContainer.append(action);
            });
            container.append(sectionContainer);
        });
    },

    _initActionEvents: function(action) {
        var self = this;
        switch (action.attr('type').toLowerCase()) {
            case 'submit':
                action.on('click', function() {
                    self.trigger('adoptedFormSubmitClick', self.form, self);
                    return false;
                });
                break;

            case 'reset':
                action.on('click', function() {
                    self.trigger('adoptedFormResetClick', self.form, self);
                });
                break;
        }
    },

    _clearActionsContainer: function() {
        var actionsEl = this.getActionsElement();
        if (actionsEl) {
            actionsEl.empty();
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
            this._show();
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
                this.setElement($(content).filter('.widget-content'));
                this._show();
                Oro.Events.trigger('hash_navigation_request:complete');
            } catch (error) {
                // Remove state with unrestorable content
                this.trigger('contentLoadError', this);
            }
        }, this));
    },

    _show: function() {
        this.trigger('renderStart', this.$el, this);
        this._adoptWidgetActions();
        this.show();
        this.trigger('renderComplete', this.$el, this);
    },

    show: function() {
        this.setWidToElement(this.$el);
        this._renderActions();
        this.$el.trigger('widgetize', this);
        this.trigger('widgetRender', this.$el, this);
    },

    setWidToElement: function(el) {
        el.attr('data-wid', this.getWid());
    }
});
