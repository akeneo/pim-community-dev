'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/user-context',
        'pimee/rule-manager',
        'routing',
        'pimee/template/product/tab/attribute/smart-attribute'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        UserContext,
        RuleManager,
        Routing,
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

                        const matchingRuleRelations = ruleRelations.filter(ruleRelation => ruleRelation.attribute === field.attribute.code);
                        if (matchingRuleRelations.length && field.isEditable()) {
                            const element = this.template({
                                __,
                                Routing,
                                ruleRelations: matchingRuleRelations,
                                getRuleLabel: (ruleRelation) => {
                                    const label = ruleRelation.labels[UserContext.get('catalogLocale')];
                                    if ((label || '').trim() === '') {
                                        return `[${ruleRelation.rule}]`;
                                    }

                                    return label;
                                }
                            });
                            const $element = $(element);
                            $element.on('click span', (event) => {
                                Routing.redirect(event.target.dataset.url);
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
