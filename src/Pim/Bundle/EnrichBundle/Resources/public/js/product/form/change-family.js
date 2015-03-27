 'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/config-manager',
        'text!pim/template/product/change-family'
    ],
    function ($, _, BaseForm, ConfigManager, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            template: _.template(formTemplate),
            events: {
                'click .icon-pencil': 'enterEditMode',
                'change select':      'changeFamily'
            },
            showFamilyList: false,
            families: [],
            configure: function () {
                this.listenTo(this.getRoot().model, 'change:family', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({
                        product: this.getData(),
                        families: this.families,
                        showFamilyList: this.showFamilyList
                    })
                );
                if (this.showFamilyList) {
                    this.$('select').select2().select2('open');
                }
                this.delegateEvents();

                return this;
            },
            enterEditMode: function () {
                ConfigManager.getEntityList('families').done(_.bind(function (families) {
                    this.families = families;
                    this.showFamilyList = true;
                    this.render();
                }, this));
            },
            changeFamily: function () {
                var selectedFamily = this.$('select').select2('val');
                this.$('select').select2('destroy');
                this.showFamilyList = false;

                this.getRoot().model.set('family', selectedFamily);
            }
        });

        return FormView;
    }
);
