'use strict';

define(
    [
        'jquery',
        'routing',
    ],
    function ($, Routing) {
        return {
            ruleRelations: {},
            ruleRelationsPromise: null,
            getRuleRelations: function (relationType) {
                if (this.ruleRelationsPromise) {
                    return this.ruleRelationsPromise.promise();
                }

                this.ruleRelationsPromise = $.Deferred();

                $.getJSON(
                    Routing.generate('pimee_enrich_rule_relation_get', {'relationType': relationType})
                ).done(_.bind(function (ruleRelations) {
                    this.ruleRelations = ruleRelations;
                    this.ruleRelationsPromise.resolve(this.ruleRelations);
                }, this));

                return this.ruleRelationsPromise.promise();
            }
        };
    }
);
