/**
 * Generate a datagrid product
 *
 * Example:
 * const DatagridProductBuilder = require('../../common/builder/datagrid-product.js');
 * const product = (new DatagridProductBuilder()).withIdentifier('shirt').build();
 */

class DatagridProductBuilder {
  constructor() {
    this.product = {
      identifier: '',
      enabled: true,
      family: 'scanner',
      label: '',
      model_type: 'product',
      completeness: 100,
      image: {
        filePath: '',
        originalFilename: ''
      },
      created: '08/26/2019',
      updated: '08/26/2019',
      complete_variant_products: [],
      id: '',
      document_type: 'product',
      technical_id: 901,
      delete_link: '/enrich/product/rest/id',
      toggle_status_link: '/enrich/product/id/toggle-status'
    }
  }

  withAttribute(name, value) {
    this.product[name] = value;

    return this;
  }

  withIdentifier(identifier) {
    this.product.identifier = identifier;

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

module.exports = DatagridProductBuilder;