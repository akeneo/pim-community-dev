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
                'click > header > .close': 'closePanel'
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

                var currentPanelExtension = this.extensions[this.getRoot().state.get('currentPanel')];
                console.log(currentPanelExtension.parent.code, 'triggered the rendering of extension', currentPanelExtension.code);
                this.$el.append(currentPanelExtension.render().$el);

                //TODO: check that it exists
                console.log(this.getParent().$('.form-container'));
                this.getParent().$('.form-container').append(this.$el);

                var selectorExtension = this.extensions['selector'];

                console.log(selectorExtension.parent.code, 'triggered the rendering of extension', selectorExtension.code);
                this.getParent().$('>header').append(selectorExtension.render().$el);

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
