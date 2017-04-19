'use strict';
/**
 * Display a vertical column for navigation or filters
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
        'text!pim/template/form/column'
    ],
    function (_, __, BaseForm, template) {
        return BaseForm.extend({
            className: 'AknColumn',

            template: _.template(template),

            events: {
                'click .AknColumn-collapseButton': '_toggleColumn'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    labelInfos: __('pim_enrich.entity.product.infos')
                }));

                this.renderExtensions();

                return this;
            },

            /**
             * {@inheritdoc}
             */
            _toggleColumn: function (e) {
                $(this.$el).toggleClass('AknColumn--collapsed');
            }
        });
    }
);
