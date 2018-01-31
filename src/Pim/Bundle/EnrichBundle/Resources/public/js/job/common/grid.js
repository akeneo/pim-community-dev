'use strict';

/**
 * Grid renderer for last job execution list
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form',
        'pim/common/grid',
        'pim/user-context'
    ],
    function (
        BaseForm,
        Grid,
        UserContext
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
                var metaData = this.config.metadata || {};
                metaData[this.config.localeKey || 'localeCode'] = UserContext.get('catalogLocale');
                metaData.jobCode = this.getFormData().code;

                this.grid = new Grid(this.config.alias, metaData);

                this.$el.empty().append(this.grid.render().$el);

                return this;
            }
        });
    });
