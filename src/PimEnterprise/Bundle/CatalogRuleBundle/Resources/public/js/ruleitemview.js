define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'routing', 'oro/mediator', 'pimee/catalogrule/itemview'],
    function ($, _, Backbone, __, Routing, mediator, ItemView) {
        'use strict';

        var templates = {
            'conditions': {
                'field': _.template(
                    '<div class="rule-item rule-condition">' +
                        '<span class="rule-item-emphasize"><%= if_label %></span>' +
                        '<span class="condition-field">' +
                            '<%= rulePart.field %>' +
                            '<%= renderItemContext(rulePart.locale, rulePart.scope) %>' +
                        '</span>' +
                        '<span class="rule-item-emphasize condition-operator"><%= rulePart.operator %></span>' +
                        '<% if (!!rulePart.value) { %>' +
                            '<span class="condition-value"><%= rulePart.value %></span>' +
                        '<% } %>' +
                    '</div>'
                )
            },
            'actions': {
                'set_value': _.template(
                    '<div class="rule-item rule-action set-value-action">' +
                        '<span class="rule-item-emphasize"><%= then_label %></span>' +
                        '<% if (typeof rulePart.value === \'object\') { %>' +
                            '<span class="action-values" title="<%= JSON.stringify(rulePart.value).replace(/\"/g, \'\\\'\') %>" >' +
                                '<%= JSON.stringify(rulePart.value) %>' +
                            '</span>' +
                        '<% } else { %>' +
                            '<span class="action-values" >' +
                                '<%= rulePart.value %>' +
                            '</span>' +
                        '<% } %>' +
                        '<span class="rule-item-emphasize action-type"><%= set_value_label %></span>' +
                        '<span class="action-field">' +
                            '<%= rulePart.field %>' +
                            '<%= renderItemContext(rulePart.locale, rulePart.scope) %>' +
                        '</span>' +
                    '</div>'
                ),
                'copy_value': _.template(
                    '<div class="rule-item rule-action copy-value-action">' +
                        '<span class="rule-item-emphasize"><%= then_label %></span>' +
                        '<span class="action-field from-field">' +
                            '<%= rulePart.from_field %>' +
                            '<%= renderItemContext(rulePart.from_locale, rulePart.from_scope) %>' +
                        '</span>' +
                        '<span class="rule-item-emphasize action-type"><%= copy_value_label %></span>' +
                        '<span class="action-field to-field">' +
                            '<%= rulePart.to_field %>' +
                            '<%= renderItemContext(rulePart.to_locale, rulePart.to_scope) %>' +
                        '</span>' +
                    '</div>'
                )
            }
        };

        var itemContextTemplate = _.template(
            '<% if (localeCountry || scope) { %>' +
                '<span class="rule-item-context">' +
                    '<% if (localeCountry) { %>' +
                        '<span class="locale">' +
                            '<span class="flag-language">' +
                                '<i class="flag flag-<%= localeCountry %>"></i>' +
                            '</span>' +
                            '<span class="locale"><%= localeLanguage %></span>' +
                        '</span>' +
                    '<% } %>' +
                    '<% if (scope) { %>' +
                        '<span class="scope">' +
                            '<%= scope %>' +
                        '</span>' +
                    '<% } %>' +
                '</span>' +
            '<% } %>'
        );

        return ItemView.extend({
            className: 'rule-row',
            template: _.template(
                '<!-- PimEnterprise/Bundle/CatalogRuleBundle/Resources/public/js/ruleitemview.js -->' +
                '<td class="rule-cell rule-code"><%= rule.code %></td>' +
                '<td class="rule-cell rule-conditions">' +
                    '<%= conditions %>' +
                '</td>' +
                '<td class="rule-cell rule-actions">' +
                    '<%= actions %>' +
                '</td>' +
                '<td class="rule-cell">' +
                    '<button class="btn delete-row" alt="Delete rule" type="button"><i class="icon-trash"></i> <%= delete_label %></button>' +
                '</td>'
            ),
            renderRuleParts: function(type) {
                var renderedRuleParts = '';

                for (var key in this.model.attributes[type]) {
                    renderedRuleParts += this.renderRulePart(this.model.attributes[type][key], type);
                }

                return renderedRuleParts;
            },
            renderRulePart: function(rulePart, type) {
                var rulePartType = rulePart.type ? rulePart.type : 'field';

                return templates[type][rulePartType]({
                    'rulePart': rulePart,
                    'renderItemContext': function(locale, scope) {
                        var localeCountry  = locale ? locale.split('_')[1].toLowerCase() : locale;
                        var localeLanguage = locale ? locale.split('_')[0].toLowerCase() : locale;

                        return itemContextTemplate({'localeCountry': localeCountry, 'localeLanguage': localeLanguage, 'scope': scope});
                    },
                    'if_label': __('pimee_catalog_rule.rule.condition.if.label'),
                    'then_label': __('pimee_catalog_rule.rule.action.then.label'),
                    'set_value_label': __('pimee_catalog_rule.rule.action.set_value.label'),
                    'copy_value_label': __('pimee_catalog_rule.rule.action.copy_value.label')
                });
            },
            renderTemplate: function() {
                var renderedConditions = this.renderRuleParts('conditions');
                var renderedActions    = this.renderRuleParts('actions');

                return this.template({
                    'rule':       this.model.toJSON(),
                    'conditions': renderedConditions,
                    'actions':    renderedActions,
                    'delete_label': __('pimee_catalog_rule.attribute.list.delete_rule.label')
                });
            },
        });
    }
);
