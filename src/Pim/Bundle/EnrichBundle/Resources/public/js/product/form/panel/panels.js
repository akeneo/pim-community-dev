'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/panel/container'
    ],
    function(_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-container closed',
            events: {
                'click .panel-selector': 'changePanel',
                'click .panel-container > header > .close': 'closePanel'
            },
            configure: function() {
                this.getRoot().addPanel = _.bind(this.addPanel, this);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addPanel: function (code, label) {
                var state = this.getRoot().state;

                var panels = state.get('panels') || [];
                panels.push({ code: code, label: label });

                state.set('panels', panels);

                if (state.get('currentPanel') === undefined) {
                    state.set('currentPanel', panels[0].code);
                }
            },
            setParent: function (parent) {
                parent.addPanel = this.addPanel;

                BaseForm.prototype.setParent.apply(this, arguments);

                return this;
            },
            render: function () {
                this.$el.html(
                    this.template({
                        state: this.getRoot().state.toJSON()
                    })
                );

                _.each(this.extensions, _.bind(function(extension) {
                    if (extension.code === this.getRoot().state.get('currentPanel')) {
                        console.log(extension.parent.cid, 'triggered the rendering of extension', extension.cid);
                        this.$el.append(extension.render().$el);
                    }
                }, this));

                this.getParent().$('.form-container').append(this.$el);

                _.each(this.extensions, _.bind(function(extension) {
                    if (extension.code === 'selector') {
                        console.log(extension.parent.cid, 'triggered the rendering of extension', extension.cid);
                        this.getParent().$('>header').append(extension.render().$el);
                    }
                }, this));

                this.delegateEvents();

                return this;
            },
            changePanel: function (event) {
                // this.$el.
                this.getRoot().state.set('currentPanel', event.currentTarget.dataset.panel);

                this.render();
                this.$el.removeClass('closed');
            },
            closePanel: function () {
                this.$el.addClass('closed');
            }
        });
    }
);
