'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/panel/container'
    ],
    function(_, Backbone, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-container closed',
            events: {
                'click > header > .close': 'closePanel'
            },
            initialize: function () {
                this.state = new Backbone.Model();

                this.listenTo(this.state, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function() {
                this.getRoot().addPanel = _.bind(this.addPanel, this);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addPanel: function (code, label) {
                var panels = this.state.get('panels') || [];
                panels.push({ code: code, label: label });

                this.state.set('panels', panels, {silent: true});

                this.state.trigger('change');
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el[this.state.get('currentPanel') ? 'removeClass' : 'addClass']('closed');

                this.$el.html(
                    this.template({
                        state: this.state.toJSON()
                    })
                );

                if (this.state.get('currentPanel')) {
                    var currentPanelExtension = this.extensions[this.state.get('currentPanel')];
                    console.log(this.code, 'triggered the rendering of', currentPanelExtension.code);
                    this.$el.append(currentPanelExtension.render().$el);
                }

                //TODO: check that it exists
                console.log(this.getParent().$('.form-container'));
                this.getParent().$('.form-container').append(this.$el);

                var selectorExtension = this.extensions['selector'];

                console.log(this.code, 'triggered the rendering of', selectorExtension.code);
                this.getParent().$('>header').append(selectorExtension.render().$el);

                this.delegateEvents();

                return this;
            },
            closePanel: function () {
                this.state.set('currentPanel', null);
            }
        });
    }
);
