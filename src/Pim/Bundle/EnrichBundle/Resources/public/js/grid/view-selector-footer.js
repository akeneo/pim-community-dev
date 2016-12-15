'use strict';

/**
 * Footer extension for the Datagrid View Selector.
 * Allow user to insert extension in data drop zone.
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/grid/view-selector/footer'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());

                this.renderExtensions();

                return this;
            }
        });
    }
);
