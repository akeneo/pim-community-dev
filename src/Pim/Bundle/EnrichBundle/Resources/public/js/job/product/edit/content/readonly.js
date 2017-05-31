/**
 * Extension to set all filters in readonly mode.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'pim/form'
],
function (
    $,
    _,
    Backbone,
    BaseForm
) {
    return BaseForm.extend({
        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:filter:extension:add', this.addFilterExtension.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Sets filters in readonly mode.
         *
         * @param {Object} event
         */
        addFilterExtension: function (event) {
            event.filter.setEditable(false);
        }
    });
});
