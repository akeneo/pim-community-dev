'use strict';

define(
    [
        'jquery',
        'underscore',
        'routing'
    ],
    function ($, _, Routing) {
        return {
            ruleRelationsPromise: null,
            getRuleRelations: function (relationType) {
                if (this.ruleRelationsPromise) {
                    return this.ruleRelationsPromise.promise();
                }

                this.ruleRelationsPromise = $.Deferred();

                $.getJSON(
                    Routing.generate('pimee_enrich_rule_relation_get', {'relationType': relationType})
                ).done(_.bind(function (ruleRelations) {
                    this.ruleRelationsPromise.resolve(ruleRelations);
                }, this));

                return this.ruleRelationsPromise.promise();
            }
        };
    }
);
