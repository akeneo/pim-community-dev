/**
 * Generate an asset family
 *
 * Example:
 * const AssetBuilder = require('../../common/builder/asset.js');
 * const asset = (new AssetBuilder()).withAssetFamilyIdentifier('designer').build();
 */

class AssetBuilder {
  constructor() {
    this.asset = {
      asset_family_identifier: '',
      code: '',
      labels: {},
      image: null,
    };
  }

  withAssetFamilyIdentifier(assetFamilyIdentifier) {
    this.asset.asset_family_identifier = assetFamilyIdentifier;

    return this;
  }

  withCode(code) {
    this.asset.code = code;
    this.asset.identifier = `${code}_123456`;

    return this;
  }

  withLabels(labels) {
    this.asset.labels = labels;

    return this;
  }

  withImage(image) {
    this.asset.image = image;

    return this;
  }

  build() {
    return this.asset;
  }
}

module.exports = AssetBuilder;
