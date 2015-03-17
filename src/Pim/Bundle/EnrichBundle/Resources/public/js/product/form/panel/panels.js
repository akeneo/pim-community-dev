'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/container',
        'text!pim/template/product/selector'
    ],
    function(_, BaseForm, containerTemplate, selectorTemplate) {
        return BaseForm.extend({
            containerTemplate: _.template(containerTemplate),
            selectorTemplate: _.template(selectorTemplate),
            className: 'panel-container',
            events: {
                'click .panel-selector': 'changePanel'
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
                    this.containerTemplate({
                        state: this.getRoot().state.toJSON()
                    })
                );

                this.getParent().$('.form-container').append(this.$el);
                this.getParent().$('>header').append(
                    this.selectorTemplate({
                        state: this.getRoot().state.toJSON()
                    })
                );
                this.delegateEvents();

                BaseForm.prototype.render.apply(this, arguments);

                return this;
            },
            changePanel: function (event) {
                this.getRoot().state.set('currentPanel', event.currentTarget.dataset.panel);

                this.render();
            }
        });
    }
);
