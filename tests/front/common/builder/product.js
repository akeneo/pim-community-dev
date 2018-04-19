/**
 * Generate a product
 *
 * Example:
 * const ProductBuilder = require('../../common/builder/product.js');
 * const product = (new ProductBuilder()).setIdentifier('shirt').build();
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

  setIdentifier(identifier) {
    this.product.identifier = identifier;
  }

  setValues(values) {
    this.product.values = values;
  }

  setEnabled(enabled) {
    this.product.enabled = enabled;
  }

  setFamily(family) {
    this.product.family = family;
  }

  setLabel(label) {
    this.product.label = label;
  }

  setModelTypel(modelType) {
    this.product.model_type = modelType;
  }

  setImage(image) {
    this.product.image = image;
  }

  setCompleteness(completeness) {
    this.product.completeness = completeness;
  }

  build() {
    return this.product;
  }
}

module.exports = ProductBuilder;
