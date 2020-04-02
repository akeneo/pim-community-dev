'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/user-context',
        'pimee/rule-manager',
        'pimee/template/product/tab/attribute/smart-attribute'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        UserContext,
        RuleManager,
        smartAttributeTemplate
    ) {
        return BaseForm.extend({
            template: _.template(smartAttributeTemplate),
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments),
                    RuleManager.getRuleRelations('attribute')
                );
            },
            addFieldExtension: function (event) {
                event.promises.push(
                    RuleManager.getRuleRelations('attribute').done(function (ruleRelations) {
                        var deferred = $.Deferred();
                        var field = event.field;
                        var ruleRelation = _.findWhere(ruleRelations, {attribute: field.attribute.code});

                        if (ruleRelation && field.isEditable()) {
                            let ruleLabel = ruleRelation.labels[UserContext.get('catalogLocale')];
                            if (undefined === ruleLabel) {
                                ruleLabel = '[' + ruleRelation.rule + ']';
                            }
                            var $element = this.template({
                                ruleLabel: ruleLabel
                            });

                            field.addElement('footer', 'from_smart', $element);
                        }
                        deferred.resolve();

                        return deferred.promise();
                    }.bind(this))
                );

                return this;
            }
        });
    }
);
