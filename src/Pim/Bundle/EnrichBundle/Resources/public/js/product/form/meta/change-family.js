 'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/entity-manager',
        'text!pim/template/product/meta/change-family',
        'pim/dialog',
        'pim/user-context'
    ],
    function ($, _, BaseForm, EntityManager, formTemplate, Dialog, UserContext) {
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

                if (this.$('select').length) {
                    this.$('select').select2('destroy');
                }

                this.$el.html(
                    this.template({
                        product: this.getData(),
                        families: this.families,
                        showFamilyList: this.showFamilyList,
                        locale: UserContext.get('catalogLocale')
                    })
                );
                if (this.showFamilyList) {
                    this.$('select').select2().select2('open');
                }
                this.delegateEvents();

                return this;
            },
            enterEditMode: function () {
                EntityManager.getRepository('family').findAll().done(_.bind(function (families) {
                    this.families = families;
                    this.showFamilyList = true;
                    this.render();
                }, this));
            },
            changeFamily: function () {
                Dialog.confirm(
                    [
                        _.__('pim_enrich.entity.product.confirmation.change_family.message'),
                        _.__('pim_enrich.entity.product.confirmation.change_family.merge_attributes'),
                        _.__('pim_enrich.entity.product.confirmation.change_family.keep_attributes')
                    ].join('</br>'),
                    _.__('pim_enrich.entity.product.confirmation.change_family.title')
                ).done(_.bind(function () {
                    var selectedFamily = this.$('select').select2('val') || null;
                    this.showFamilyList = false;
                    this.getRoot().model.set('family', selectedFamily);
                }, this)).always(_.bind(function () {
                    this.showFamilyList = false;
                    this.render();
                }, this));
            }
        });

        return FormView;
    }
);
