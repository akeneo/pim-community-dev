'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pimee/rule-manager',
        'oro/mediator',
        'text!pimee/template/product/tab/attribute/smart-attribute'
    ],
    function ($, _, Backbone, BaseForm, FieldManager, RuleManager, mediator, smartAttributeTemplate) {
        return BaseForm.extend({
            template: _.template(smartAttributeTemplate),
            configure: function () {
                mediator.on('field:extension:add', _.bind(this.addExtension, this));

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addExtension: function (event) {
                RuleManager.getRuleRelations('attribute').done(_.bind(function (ruleRelations) {
                    var field = event.field;
                    var ruleRelation = _.findWhere(ruleRelations, {attribute: field.attribute.code});

                    if (ruleRelation && 'edit' === field.getEditMode()) {
                        var $element = this.template({
                            ruleRelation: ruleRelation
                        });

                        field.addElement('footer', 'updated_by', $element);
                    }
                }, this));

                return this;
            }
        });
    }
);
