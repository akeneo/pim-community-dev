'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pimee/rule-manager',
        'text!pimee/template/product/tab/attribute/smart-attribute'
    ],
    function(_, Backbone, BaseForm, FieldManager, RuleManager, smartAttributeTemplate) {
        return BaseForm.extend({
            template: _.template(smartAttributeTemplate),
            render: function() {
                RuleManager.getRuleRelations('attribute').done(_.bind(function(ruleRelations) {
                    var fields = FieldManager.getFields();

                    _.each(fields, _.bind(function(field) {
                        var ruleRelation = _.findWhere(ruleRelations, {attribute: field.attribute.code});
                        if (ruleRelation && 'edit' === field.getEditMode()) {
                            var $element = this.template({
                                ruleRelation: ruleRelation
                            });

                            field.addElement('footer', 'updated_by', $element);
                        }
                    }, this));
                }, this));

                return this;
            }
        });
    }
);
