/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'pim/form/common/edit-form'
], function (
    BaseEditForm
) {
    return BaseEditForm.extend({
        additionalViews: {},

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.on('pim_enrich:form:entity:post_fetch', this.render);

            return BaseEditForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Sets a view name for an arbitrary key, to be used later for dynamic tree building purpose.
         *
         * @param {String} key
         * @param {String} viewName
         */
        setAdditionalView: function (key, viewName) {
            this.additionalViews[key] = viewName;
        },

        /**
         * Returns the view name associated to the key.
         *
         * @param {String} key
         *
         * @return {String}
         */
        getAdditionalView: function (key) {
            return this.additionalViews[key];
        }
    });
});
