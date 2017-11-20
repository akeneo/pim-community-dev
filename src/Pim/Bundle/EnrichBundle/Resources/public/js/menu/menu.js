'use strict';

/**
 * Base extension for menu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'pim/template/menu/menu'
    ],
    function (
        _,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'AknHeader',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().append(this.template());

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            renderExtension: function (extension) {
                if (!_.isEmpty(extension.options.config) &&
                    !extension.options.config.to &&
                    _.isFunction(extension.hasChildren) &&
                    !extension.hasChildren()) {
                    return;
                }

                BaseForm.prototype.renderExtension.apply(this, arguments);
            }
        });
    });
