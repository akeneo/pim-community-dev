const BaseMainImage = require('pim/form/common/main-image');
const FetcherRegistry = require('pim/fetcher-registry');
const Routing = require('routing');

class MainImage extends BaseMainImage {
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', this.render.bind(this));
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render.bind(this));

    return BaseMainImage.prototype.configure.apply(this, arguments);
  };

  render() {
    const fallbackUrl = '/bundles/pimui/img/image_default.png';

    FetcherRegistry.getFetcher('family').fetch(this.getRoot().getFormData().family).then((family: any) => {
      const attributeCode = family.attribute_as_image;
      if (!attributeCode) {
        this.el.src = fallbackUrl;
        return;
      }

      const imageValues = this.getRoot().getFormData().values[attributeCode];
      if (imageValues.length === 0) {
        this.el.src = fallbackUrl;
        return;
      }

      const imageValue = imageValues[0]; // As the attribute_as_image is not localizable, it can only have 1 value max.
      const imageData = imageValue.data;
      if (imageData.hasOwnProperty('filePath') && imageData.hasOwnProperty('originalFilename')) {
        this.el.src = Routing.generate('pim_enrich_media_show', { 'filename': encodeURIComponent(imageData.filePath), 'filter': 'thumbnail' });
        return;
      }

      this.el.src = fallbackUrl;
    });
  };
}

export = MainImage;
