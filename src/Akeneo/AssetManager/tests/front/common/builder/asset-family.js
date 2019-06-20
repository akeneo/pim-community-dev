/**
 * Generate an asset family
 *
 * Example:
 * const AssetFamilyBuilder = require('../../common/builder/asset-family.js');
 * const assetFamily = (new AssetFamilyBuilder()).withIdentifier('designer').build();
 */

class AssetFamilyBuilder {
  constructor() {
    this.assetFamily = {
      identifier: '',
      labels: {},
      image: null,
      attributes: [],
      asset_count: 123,
      attribute_as_label: '',
      attribute_as_image: '',
      permission: {edit: true},
    };
  }

  withIdentifier(identifier) {
    this.assetFamily.identifier = identifier;

    return this;
  }

  withLabels(labels) {
    this.assetFamily.labels = labels;

    return this;
  }

  withImage(image) {
    this.assetFamily.image = image;

    return this;
  }

  withAttributes(attributes) {
    this.assetFamily.attributes = attributes;

    return this;
  }

  withAttributeAsImage(attribute) {
    this.assetFamily.attribute_as_image = attribute;

    return this;
  }

  withAttributeAsLabel(attribute) {
    this.assetFamily.attribute_as_label = attribute;

    return this;
  }

  withPermission(permission) {
    this.assetFamily.permission = permission;

    return this;
  }

  build() {
    return this.assetFamily;
  }
}

module.exports = AssetFamilyBuilder;
