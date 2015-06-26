define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator',
        'routing',
        'oro/mediator',
        'oro/loading-mask',
        'pim/item/tableview',
        'pimee/catalogrule/ruleitemview'
    ],
    function ($, _, Backbone, __, Routing, mediator, LoadingMask, TableView, RuleItemView) {
        'use strict';

        var RuleItem = Backbone.Model.extend({
            defaults: {
                priority: 0,
                content: []
            }
        });

        var ItemCollection = Backbone.Collection.extend({
            model: RuleItem,
            initialize: function (options) {
                this.url = options.url;
            }
        });

        var RuleCollectionView = TableView.extend({
            className: 'rule-table',
            template: _.template(
                '<!-- PimEnterprise/Bundle/CatalogRuleBundle/Resources/public/js/ruletableview.js -->' +
                '<colgroup>' +
                    '<col class="code"></col>' +
                    '<col class="condition"></col>' +
                    '<col class="action"></col>' +
                    '<col class="button"></col>' +
                '</colgroup>' +
                '<thead>' +
                    '<tr>' +
                        '<th><%= code_label %></th>' +
                        '<th><%= condition_label %></th>' +
                        '<th><%= action_label %></th>' +
                        '<th></th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>' +
                '</tbody>'
            ),
            emptyTemplate: _.template(
                '<!-- PimEnterprise/Bundle/CatalogRuleBundle/Resources/public/js/ruletableview.js -->' +
                '<tfoot>' +
                    '<tr>' +
                        '<td class="no-rule"><%= no_rule_yet_label %></td>' +
                    '</tr>' +
                '</tfoot>'
            ),
            renderTemplate: function () {
                if (this.collection.models.length > 0) {
                    return this.template({
                        'code_label':      __('pimee_catalog_rule.attribute.list.code.label'),
                        'condition_label': __('pimee_catalog_rule.attribute.list.condition.label'),
                        'action_label':    __('pimee_catalog_rule.attribute.list.action.label')
                    });
                } else {
                    return this.emptyTemplate({
                        'no_rule_yet_label': __('pimee_catalog_rule.attribute.list.no_rule_yet.label')
                    });
                }
            }
        });

        return function ($element) {
            /* jshint nonew:false */
            new RuleCollectionView({
                $target: $element,
                url: Routing.generate(
                    'pimee_catalog_rule_index',
                    {
                        resourceId: $element.data('attribute-id'),
                        resourceName: 'attribute'
                    }
                ),
                collectionClass: ItemCollection,
                itemClass: RuleItem,
                itemViewClass: RuleItemView
            });
        };
    }
);
