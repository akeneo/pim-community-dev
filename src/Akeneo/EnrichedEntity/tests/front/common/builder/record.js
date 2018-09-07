/**
 * Generate an enriched entity
 *
 * Example:
 * const RecordBuilder = require('../../common/builder/record.js');
 * const record = (new RecordBuilder()).withEnrichedEntityIdentifier('designer').build();
 */

class RecordBuilder {
  constructor() {
    this.record = {
      enriched_entity_identifier: '',
      code: '',
      labels: {},
      image: null,
    };
  }

  withEnrichedEntityIdentifier(enrichedEntityIdentifier) {
    this.record['enriched_entity_identifier'] = enrichedEntityIdentifier;

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
