'use strict';
/**
 * Structure section
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'text!pim/template/export/product/edit/content/structure',
        'pim/form'
    ],
    function (
        _,
        __,
        template,
        BaseForm
    ) {
        return BaseForm.extend({
            className: 'structure-filters',

            template: _.template(template),

            /**
             * Renders this view.
             *
             * @return {Object}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.$el.html(this.template({__: __}));

                this.renderExtensions();

                return this;
            }
        });
    }
);
