'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/form-tabs'
    ],
    function(_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'tabbable tabs-top',
            events: {
                'click header ul.nav-tabs li': 'changeTab'
            },
            configure: function() {
                this.getRoot().addTab = _.bind(this.addTab, this);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addTab: function (code, label) {
                var state = this.getRoot().state;

                var tabs = state.get('tabs') || [];
                tabs.push({ code: code, label: label });

                state.set('tabs', tabs, {silent: true});

                if (state.get('currentTab') === undefined) {
                    state.set('currentTab', tabs[0].code, {silent: true});
                }

                state.trigger('change');
            },
            setParent: function (parent) {
                parent.addTab = this.addTab;

                BaseForm.prototype.setParent.apply(this, arguments);

                return this;
            },
            render: function () {
                this.$el.html(
                    this.template({
                        state: this.getRoot().state.toJSON()
                    })
                );
                this.$el.insertAfter(this.getRoot().$('>div>header'));
                this.delegateEvents();

                this.extensions[this.getRoot().state.get('currentTab')].render();

                return this;
            },
            changeTab: function (event) {
                this.getRoot().state.set('currentTab', event.currentTarget.dataset.tab);

                this.render();
            }
        });
    }
);
