'use strict';

/**
 * Base extension forheadermenu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'pim/template/header/header'
    ],
    function (
        _,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            tagName: 'header',
            className: 'AknHeader',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                return BaseForm.prototype.render.apply(this, arguments);
            }
        });
    });
