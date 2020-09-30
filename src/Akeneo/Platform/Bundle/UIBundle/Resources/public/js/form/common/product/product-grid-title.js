'use strict';
/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
  [
    'pim/common/grid-title',
    'oro/translator',
  ], function (
    BaseGridTitle,
    __,
  ) {
    return BaseGridTitle.extend({
      totalProducts: null,
      totalProductModels: null,

      /**
       * Setup the count from the collection
       *
       * @param {Object} collection
       */
      setupCount(collection) {
        this.totalProducts = collection.state.totalProducts;
        this.totalProductModels = collection.state.totalProductModels;

        return BaseGridTitle.prototype.setupCount.apply(this, arguments);
      },

      /**
       * {@inheritdoc}
       */
      render() {
        if (!this.totalProducts && !this.totalProductModels) {
          this.$el.html(
            __(this.config.title, {count: this.count}, this.count)
          );

          return this;
        }

        const productCount = __(
          'pim_enrich.entity.product.page_title.product',
          { count: this.totalProducts },
          this.totalProducts
        );
        const productModelCount = __(
          'pim_enrich.entity.product.page_title.product_model',
          { count: this.totalProductModels },
          this.totalProductModels
        );
        if (this.totalProducts && !this.totalProductModels) {
          this.$el.html(productCount);
        } else if (!this.totalProducts && this.totalProductModels) {
          this.$el.html(productModelCount);
        } else {
          this.$el.html(__(
            'pim_enrich.entity.product.page_title.product_and_product_model',
            { productCount, productModelCount }
          ));
        }

        return this;
      },

    });
});
