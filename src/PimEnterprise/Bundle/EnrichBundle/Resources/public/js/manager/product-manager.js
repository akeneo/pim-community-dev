'use strict';

define([
        'jquery',
        'underscore',
        'pim/product-manager'
    ], function (
        $,
        _,
        ProductManager
    ) {
        return _.extend(ProductManager, {
            generateMissing: function (product) {
                return ProductManager._generateMissing(product).then(function (product) {
                    var workingCopyPromise = new $.Deferred();

                    if (product.meta.working_copy) {
                        workingCopyPromise = $.when.apply($, [
                            ProductManager._generateMissing(product.meta.working_copy),
                            product
                        ])
                        .then(function (workingCopy, product) {
                            product.meta.working_copy = workingCopy;

                            return product;
                        });

                    } else {
                        workingCopyPromise.resolve(product);
                    }

                    return workingCopyPromise.promise();
                });
            }
        });
    }
);
