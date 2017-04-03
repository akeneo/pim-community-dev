'use strict';
/**
 * Generic grid renderer
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form',
        'pim/common/grid'
    ],
    function (
        BaseForm,
        Grid
    ) {
        return BaseForm.extend({
            grid: null,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.grid) {
                    this.grid = new Grid(this.config.alias, {});
                }

                this.$el.empty().append(this.grid.render().$el);

                return this;
            }
        });
    });
