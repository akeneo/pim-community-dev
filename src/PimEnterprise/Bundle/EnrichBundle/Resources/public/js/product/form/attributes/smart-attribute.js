'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pimee/rule-manager',
        'oro/mediator',
        'text!pimee/template/product/tab/attribute/smart-attribute'
    ],
    function ($, _, Backbone, BaseForm, RuleManager, mediator, smartAttributeTemplate) {
        return BaseForm.extend({
            template: _.template(smartAttributeTemplate),
            configure: function () {
                this.listenTo(mediator, 'pim_enrich:form:field:extension:add', this.addExtension);

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments),
                    RuleManager.getRuleRelations('attribute')
                );
            },
            addExtension: function (event) {
                event.promises.push(
                    RuleManager.getRuleRelations('attribute').done(_.bind(function (ruleRelations) {
                        var deferred = $.Deferred();
                        var field = event.field;
                        var ruleRelation = _.findWhere(ruleRelations, {attribute: field.attribute.code});

                        if (ruleRelation && field.isEditable()) {
                            var $element = this.template({
                                ruleRelation: ruleRelation
                            });

                            field.addElement('footer', 'from_smart', $element);
                        }
                        deferred.resolve();

                        return deferred.promise();
                    }, this))
                );

                return this;
            }
        });
    }
);
