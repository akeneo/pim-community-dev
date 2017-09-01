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
        type: null,

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.on('pim_enrich:form:entity:post_fetch', this.render);

            return BaseEditForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Sets the attribute type for dynamic tree building purpose at configuration time.
         *
         * @param {String} type
         */
        setType: function (type) {
            this.type = type;
        },

        /**
         * Returns the view name associated to the key.
         *
         * @return {String}
         */
        getType: function () {
            return this.type;
        },

        /**
         * {@inheritdoc}
         *
         * Little hack to restore the scroll position after a complete re-render of the form.
         */
        render: function () {
            var scrollPosition = this.$el.find('.edit-form').scrollTop();

            BaseEditForm.prototype.render.apply(this, arguments);

            this.$el.find('.edit-form').scrollTop(scrollPosition);
        }
    });
});
