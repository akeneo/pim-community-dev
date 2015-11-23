define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'routing', 'oro/mediator', 'pim/item/view'],
    function ($, _, Backbone, __, Routing, mediator, ItemView) {
        'use strict';

        var ruleItemTemplates = {
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
                            '<span class="condition-value"><%= renderValue(rulePart.value) %></span>' +
                        '<% } %>' +
                    '</div>'
                )
            },
            'actions': {
                'set': _.template(
                    '<div class="rule-item rule-action set-value-action">' +
                        '<span class="rule-item-emphasize"><%= then_label %></span>' +
                        '<span class="action-value"><%= renderValue(rulePart.data) %></span>' +
                        '<span class="rule-item-emphasize action-type set-value"><%= set_label %></span>' +
                        '<span class="action-field">' +
                            '<%= rulePart.field %>' +
                            '<%= renderItemContext(rulePart.locale, rulePart.scope) %>' +
                        '</span>' +
                    '</div>'
                ),
                'copy': _.template(
                    '<div class="rule-item rule-action copy-value-action">' +
                        '<span class="rule-item-emphasize"><%= then_label %></span>' +
                        '<span class="action-field from-field">' +
                            '<%= rulePart.from_field %>' +
                            '<%= renderItemContext(rulePart.from_locale, rulePart.from_scope) %>' +
                        '</span>' +
                        '<span class="rule-item-emphasize action-type copy-value"><%= copy_label %></span>' +
                        '<span class="action-field to-field">' +
                            '<%= rulePart.to_field %>' +
                            '<%= renderItemContext(rulePart.to_locale, rulePart.to_scope) %>' +
                        '</span>' +
                    '</div>'
                )
            }
        };

        var valueTemplates = {
            'metric': _.template('<%= value.data %> <%= value.unit %>'),
            'collection': _.template(
                '<% for (var i in value) { %>' +
                    '<%= renderValue(value[i]) %>' +
                    '<% if (i < value.length - 1) { %>, ' + '<% } %>' +
                '<% } %>'
            ),
            'price': _.template('<%= value.data %> <%= value.currency %> '),
            'file': _.template('<i class="icon-file"></i> <%= value.originalFilename %>'),
            'default': _.template('<%= value %>')
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

        var renderValue = function (value) {
            var template;

            switch (true) {
                case typeof value.unit !== 'undefined':
                    template = 'metric';
                    break;
                case typeof value.currency !== 'undefined':
                    template = 'price';
                    break;
                case typeof value.originalFilename !== 'undefined':
                    template = 'file';
                    break;
                case Array.isArray(value):
                    template = 'collection';
                    break;
                default:
                    template = 'default';
                    break;
            }

            return valueTemplates[template]({'value': value, 'renderValue': renderValue});
        };

        return ItemView.extend({
            className: 'rule-row',
            itemName: 'rule',
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
                    '<button class="btn delete-row" alt="Delete rule" type="button">' +
                        '<i class="icon-trash"></i> <%= delete_label %>' +
                    '</button>' +
                '</td>'
            ),
            renderRuleContentParts: function (type) {
                var renderedRuleContentParts = '';
                var ruleContentPart = this.model.attributes.content[type.toString()];

                for (var key in ruleContentPart) {
                    renderedRuleContentParts += this.renderRulePart(ruleContentPart[key], type);
                }

                return renderedRuleContentParts;
            },
            renderRulePart: function (rulePart, type) {
                var rulePartType = rulePart.type ? rulePart.type : 'field';

                return ruleItemTemplates[type][rulePartType]({
                    'rulePart': rulePart,
                    'renderItemContext': function (locale, scope) {
                        var localeCountry  = locale ? locale.split('_')[1].toLowerCase() : locale;
                        var localeLanguage = locale ? locale.split('_')[0].toLowerCase() : locale;

                        return itemContextTemplate({
                            'localeCountry': localeCountry,
                            'localeLanguage': localeLanguage,
                            'scope': scope
                        });
                    },
                    'renderValue': renderValue,
                    'if_label': __('pimee_catalog_rule.rule.condition.if.label'),
                    'then_label': __('pimee_catalog_rule.rule.action.then.label'),
                    'set_label': __('pimee_catalog_rule.rule.action.set.label'),
                    'copy_label': __('pimee_catalog_rule.rule.action.copy.label')
                });
            },
            renderTemplate: function () {
                var renderedConditions = this.renderRuleContentParts('conditions');
                var renderedActions    = this.renderRuleContentParts('actions');

                return this.template({
                    'rule':       this.model.toJSON(),
                    'conditions': renderedConditions,
                    'actions':    renderedActions,
                    'delete_label': __('pim_enrich.item.list.delete.label')
                });
            }
        });
    }
);
