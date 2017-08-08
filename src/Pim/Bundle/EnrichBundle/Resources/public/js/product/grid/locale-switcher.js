'use strict';

/**
 * Extension to switch locales on the product grid
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'pim/form',
        'pim/template/product/grid/locale-switcher',
    ],
    function (
        $,
        _,
        BaseForm,
        template,
    ) {
        return BaseForm.extend({
            template: _.template(template),

            initialize: function (config) {
                this.config = config.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            configure: function () {
                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().append(this.template({}));
            },
        });
    });
