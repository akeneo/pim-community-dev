/**
 * Basic view that simply renders a template.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'oro/translator',
    'pim/form',
    'require-context',
    'pim/product/grid/bridge'
], function (
    $,
    _,
    Backbone,
    __,
    BaseForm,
    requireContext,
    bridge
) {
    return BaseForm.extend({
        config: {},
        template: null,

        /**
         * {@inheritdoc}
         */
        render: function () {
            bridge.default(this.el);
        }
    });
});
