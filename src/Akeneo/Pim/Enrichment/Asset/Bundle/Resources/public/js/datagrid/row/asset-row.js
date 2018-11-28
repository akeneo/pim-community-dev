/*
 * This module is a custom row for rendering an asset in the datagrid
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['jquery', 'oro/datagrid/product-row'], function($, BaseRow) {
    return BaseRow.extend({

        /**
         * {@inheritdoc}
         */
        getTemplateOptions() {
            const label = this.model.get('code');
            const thumbnail = this.model.get('thumbnail');
            const imagePath = $(thumbnail).attr('src').replace('thumbnail_small', 'preview');

            return {
                useLayerStyle: false,
                identifier: '',
                label,
                imagePath
            };
        },

        /**
         * {@inheritdoc}
         */
        getRenderableColumns() {
            return ['massAction', 'rowActions'];
        }
    });
});
