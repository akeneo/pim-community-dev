const BaseGridTitle = require('pim/common/grid-title');
const __ = require('oro/translator');

class ProductGridTitle extends BaseGridTitle {
  private totalProducts: number | null = null;
  private totalProductModels: number | null = null;

  /**
   * Setup the count from the collection
   *
   * @param {Object} collection
   */
  setupCount(collection: any): any {
    this.totalProducts = collection.state.totalProducts;
    this.totalProductModels = collection.state.totalProductModels;

    return BaseGridTitle.prototype.setupCount.apply(this, arguments);
  }

  private inLoadingStatus(): boolean {
    return null === this.count && null === this.totalProducts && null === this.totalProductModels;
  }

  private hasTotalProductCountOrProductModelCount(): boolean {
    return !!this.totalProducts || !!this.totalProductModels;
  }

  private renderProductAndProductModelCounts(): ProductGridTitle {
    const productCount = __(
      'pim_enrich.entity.product.page_title.product',
      {count: this.totalProducts},
      this.totalProducts
    );
    const productModelCount = __(
      'pim_enrich.entity.product.page_title.product_model',
      {count: this.totalProductModels},
      this.totalProductModels
    );
    if (this.totalProducts && !this.totalProductModels) {
      this.$el.html(productCount);
    } else if (!this.totalProducts && this.totalProductModels) {
      this.$el.html(productModelCount);
    } else {
      this.$el.html(
        __('pim_enrich.entity.product.page_title.product_and_product_model', {productCount, productModelCount})
      );
    }

    return this;
  }

  /**
   * {@inheritdoc}
   */
  render(): ProductGridTitle {
    if (this.inLoadingStatus()) {
      return this;
    }

    if (this.hasTotalProductCountOrProductModelCount()) {
      return this.renderProductAndProductModelCounts();
    }

    this.$el.html(__(this.config.title, {count: this.count}, this.count));

    return this;
  }
}

export default ProductGridTitle;
