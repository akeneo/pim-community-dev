'use strict';
/**
 * Displays a list of meta information
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/form/meta'
    ],
    function (_, __, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),

            config: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty();

                if (!_.isEmpty(this.extensions)) {
                    this.$el.html(this.template({
                        label: __(this.config.label)
                    }));
                }

                return BaseForm.prototype.render.apply(this, arguments);
            }
        });
    }
);
