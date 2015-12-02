'use strict';
/**
 * Completeness panel extension for published product view
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/product-edit-form/panel/completeness',
        'pim/fetcher-registry'
    ],
    function (CompletenessPanel, FetcherRegistry) {
        return CompletenessPanel.extend({
            /**
             * {@inheritdoc}
             */
            fetchCompleteness: function () {
                return FetcherRegistry.getFetcher('published-product-completeness').fetchForProduct(
                    this.getFormData().meta.id,
                    this.getFormData().family
                );
            }
        });
    }
);
