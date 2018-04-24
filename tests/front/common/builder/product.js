/**
 * Generate a product
 *
 * Example:
 * const ProductBuilder = require('../../common/builder/product.js');
 * const product = (new ProductBuilder()).withIdentifier('shirt').build();
 */

class ProductBuilder {
  constructor() {
    this.product = {
      identifier: '',
      values: [],
      enabled: true,
      family: 'scanner',
      label: {},
      model_type: 'product',
      image: null,
      completeness: []
    }
  }

  withIdentifier(identifier) {
    this.product.identifier = identifier;

    return this;
  }

  withValues(values) {
    this.product.values = values;

    return this;
  }

  withEnabled(enabled) {
    this.product.enabled = enabled;

    return this;
  }

  withFamily(family) {
    this.product.family = family;

    return this;
  }

  withLabel(label) {
    this.product.label = label;

    return this;
  }

  withModelTypel(modelType) {
    this.product.model_type = modelType;

    return this;
  }

  withImage(image) {
    this.product.image = image;

    return this;
  }

  withCompleteness(completeness) {
    this.product.completeness = completeness;

    return this;
  }

  build() {
    return this.product;
  }
}

module.exports = ProductBuilder;
