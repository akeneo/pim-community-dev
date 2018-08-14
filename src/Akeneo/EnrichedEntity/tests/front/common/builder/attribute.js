/**
 * Generate a normalized attribute
 *
 * Example:
 * const AttributeBuilder = require('../../common/builder/attribute.js');
 * const normalizedAttribute = (new AttributeBuilder('text'))
 *    .withEnrichedEntityIdentifier('designer')
 *    .withCode('description')
 *    .build();
 */

class AttributeBuilder {
  constructor(type) {
    this.normalizedAttribute = {
      type: type,
      identifier: '',
      enrichedEntityIdentifier: '',
      code: '',
      labels: {}
    };
  }

  withCode(code) {
    this.normalizedAttribute.code = code;

    return this;
  }

  withEnrichedEntityIdentifier(identifier) {
    this.normalizedAttribute.enrichedEntityIdentifier = identifier;

    return this;
  }

  withLabels(labels) {
    this.normalizedAttribute.labels = labels;

    return this;
  }

  build() {
    this.normalizedAttribute.identifier = {
      identifier: this.normalizedAttribute.code,
        enrichedEntityIdentifier: this.normalizedAttribute.enrichedEntityIdentifier
    };

    return this.normalizedAttribute;
  }
}

module.exports = AttributeBuilder;
