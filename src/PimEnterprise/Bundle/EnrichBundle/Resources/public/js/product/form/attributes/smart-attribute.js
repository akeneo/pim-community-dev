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
                mediator.off(null, null, 'context:product:form:attribute:smart-attribute');
                mediator.on(
                    'field:extension:add',
                    _.bind(this.addExtension, this),
                    'context:product:form:attribute:smart-attribute'
                );

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments),
                    RuleManager.getRuleRelations('attribute')
                );
            },
            addExtension: function (event) {
                RuleManager.getRuleRelations('attribute').done(_.bind(function (ruleRelations) {
                    var field = event.field;
                    var ruleRelation = _.findWhere(ruleRelations, {attribute: field.attribute.code});

                    if (ruleRelation && field.getEditable()) {
                        var $element = this.template({
                            ruleRelation: ruleRelation
                        });

                        field.addElement('footer', 'from_smart', $element);
                    }
                }, this));

                return this;
            }
        });
    }
);
