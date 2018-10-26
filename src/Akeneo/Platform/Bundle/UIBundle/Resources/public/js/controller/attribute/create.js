/**
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
'use strict';

define([
    'pim/controller/attribute/create'
],
function (BaseController) {
    return BaseController.extend({
        /**
         * {@inheritdoc}
         *
         * Override to add reference data name default value in the case of an assets collection.
         */
        getNewAttribute: function (type) {
            var attribute = BaseController.prototype.getNewAttribute.apply(this, arguments);

            if ('pim_assets_collection' === type) {
                attribute.reference_data_name = 'assets';
            }

            return attribute;
        }
    });
});
