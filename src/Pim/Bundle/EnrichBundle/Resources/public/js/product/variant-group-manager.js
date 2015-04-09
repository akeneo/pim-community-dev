'use strict';

define(['jquery', 'underscore', 'routing'], function ($, _, Routing) {
    return {
        urls: {
            'get_variant_group': 'pim_enrich_variant_group_rest_get'
        },
        variantGroups: {},
        variantGroupPromises: {},
        getVariantGroup: function (variantGroupCode) {
            var promise = new $.Deferred();

            if (!(variantGroupCode in this.variantGroupPromises)) {
                this.variantGroupPromises[variantGroupCode] = $.ajax(
                    Routing.generate(this.urls.get_variant_group, {'identifier': variantGroupCode}),
                    {
                        method: 'GET'
                    }
                ).promise();
            }

            if (!(variantGroupCode in this.variantGroups)) {
                this.variantGroupPromises[variantGroupCode].done(_.bind(function (data) {
                    this.variantGroups[variantGroupCode] = data;

                    promise.resolve(this.variantGroups[variantGroupCode]);
                }, this));
            } else {
                promise.resolve(this.variantGroups[variantGroupCode]);
            }

            return promise.promise();
        }
    };
});
