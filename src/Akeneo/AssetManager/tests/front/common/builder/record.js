/**
 * Generate a reference entity
 *
 * Example:
 * const RecordBuilder = require('../../common/builder/record.js');
 * const record = (new RecordBuilder()).withReferenceEntityIdentifier('designer').build();
 */

class RecordBuilder {
  constructor() {
    this.record = {
      reference_entity_identifier: '',
      code: '',
      labels: {},
      image: null,
    };
  }

  withReferenceEntityIdentifier(referenceEntityIdentifier) {
    this.record.reference_entity_identifier = referenceEntityIdentifier;

    return this;
  }

  withCode(code) {
    this.record.code = code;
    this.record.identifier = `${code}_123456`;

    return this;
  }

  withLabels(labels) {
    this.record.labels = labels;

    return this;
  }

  withImage(image) {
    this.record.image = image;

    return this;
  }

  build() {
    return this.record;
  }
}

module.exports = RecordBuilder;
