'use strict';
/**
 * Form tabs extension
 * This is an extension of the form-tabs, to be able to display select buttons in a higher element.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form/common/form-tabs',
        'pim/template/form/column-tabs'
    ],
    function ($, _, FormTabs, template) {
        return FormTabs.extend({
            className: '',

            template: _.template(template),

            currentKey: 'current_column_tab',

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'column-tab:select-tab', this.selectTab);

                return FormTabs.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            registerTab: function (event) {
                FormTabs.prototype.registerTab.apply(this, arguments);
                this.getRoot().trigger('column-tab:register', event);
            }
        });
    }
);
