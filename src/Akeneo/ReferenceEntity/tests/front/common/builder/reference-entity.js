/**
 * Generate a reference entity
 *
 * Example:
 * const ReferenceEntityBuilder = require('../../common/builder/reference-entity.js');
 * const referenceEntity = (new ReferenceEntityBuilder()).withIdentifier('designer').build();
 */

class ReferenceEntityBuilder {
  constructor() {
    this.referenceEntity = {
      identifier: '',
      labels: {},
      image: null,
      attributes: [],
      record_count: 123,
      permission: {edit: true},
    };
  }

  withIdentifier(identifier) {
    this.referenceEntity.identifier = identifier;

    return this;
  }

  withLabels(labels) {
    this.referenceEntity.labels = labels;

    return this;
  }

  withImage(image) {
    this.referenceEntity.image = image;

    return this;
  }

  withAttributes(attributes) {
    this.referenceEntity.attributes = attributes;

    return this;
  }

  withPermission(permission) {
    this.referenceEntity.permission = permission;

    return this;
  }

  build() {
    return this.referenceEntity;
  }
}

module.exports = ReferenceEntityBuilder;
