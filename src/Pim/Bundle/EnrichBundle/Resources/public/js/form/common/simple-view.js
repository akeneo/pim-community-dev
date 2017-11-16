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
    'require-context'
], function (
    $,
    _,
    Backbone,
    __,
    BaseForm,
    requireContext
) {
    return BaseForm.extend({
        config: {},
        template: null,

        /**
         * {@inheritdoc}
         */
        initialize: function (meta) {
            this.config = meta.config;

            if (_.has(meta, 'forwarded-events')) {
                this.forwardMediatorEvents(meta['forwarded-events']);
            }

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         *
         * Waiting for the template to be required.
         */
        configure: function () {
            if (undefined === this.config.template) {
                throw new Error('The view "' + this.code + '" must be configured with a template.');
            }

            this.template = requireContext(this.config.template);

            this.listenTo(this.getRoot(), 'grid:third_column:toggle', this.toggleThirdColumn.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            let templateParams = this.config.templateParams || {};
            templateParams = _.extend({}, {__: __}, templateParams);

            this.$el.html(
                _.template(this.template)(templateParams)
            );

            this.renderExtensions();
        },

        /**
         * Toggle the third column
         */
        toggleThirdColumn() {
            const thirdColumn = this.$el.find('.AknDefault-thirdColumnContainer');
            const thirdColumnContent = this.$el.find('.AknDefault-thirdColumn');
            const width = thirdColumnContent.outerWidth() || 300;

            if (null !== thirdColumn) {
                thirdColumn.css({marginLeft: -width});
                thirdColumn.toggleClass('AknDefault-thirdColumnContainer--open');
            }
        }
    });
});
