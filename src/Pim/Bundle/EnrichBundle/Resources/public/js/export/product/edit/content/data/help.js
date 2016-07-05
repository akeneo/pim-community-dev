/**
 * Extension to add a "remove" button on an optional filter.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'oro/translator',
    'pim/form',
    'text!pim/template/export/product/edit/content/data/help'

], function (__, BaseForm, template) {
    return BaseForm.extend({
        template: _.template(template),

        /**
         * {@inherit}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:filter:extension:add', this.addFilterExtension.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Adds the extension to filters.
         *
         * @param {Object} event
         */
        addFilterExtension: function (event) {
            var $content = $(this.template({
                text: __('pim_enrich.export.product.filter.' + event.filter.getField() + '.help')
            }));

            $content.find('[data-toggle="tooltip"]').tooltip();

            event.filter.addElement(
                'after-input',
                'help',
                $content
            );
        }
    });
});
