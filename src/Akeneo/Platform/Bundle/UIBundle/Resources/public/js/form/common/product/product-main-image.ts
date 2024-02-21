const MainImage = require('pim/form/common/main-image');
const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');

class ProductMainImage extends MainImage {
  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(UserContext, 'change:catalogScope change:catalogLocale', this.updateImagePath.bind(this));

    return super.configure();
  }

  private async updateImagePath() {
    const {meta} = this.getFormData();
    const catalogLocale = UserContext.get('catalogLocale');
    const catalogScope = UserContext.get('catalogScope');
    const product = await FetcherRegistry.getFetcher(meta.model_type).fetch(meta.id, {
      silent: true,
      catalogLocale,
      catalogScope,
    });

    this.imagePath = product?.meta?.image?.filePath ?? null;
    this.render();
  }
}

export = ProductMainImage;
