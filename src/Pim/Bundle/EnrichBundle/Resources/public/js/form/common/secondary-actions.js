'use strict';
/**
 * Displays a list of secondary actions
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
        'pim/template/form/secondary-actions'
    ],
    function (
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'AknSecondaryActions AknDropdown AknButtonList-item secondary-actions',

            template: _.template(template),

            /**
             * When there is no extensions attached to this module, nothing is rendered.
             * Each extension represents a secondary action available for the user.
             *
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty();

                if (!_.isEmpty(this.extensions)) {
                    this.$el.html(this.template({
                        titleLabel: __('pim_datagrid.actions.other')
                    }));

                    this.renderExtensions();
                }
            }
        });
    }
);
