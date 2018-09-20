/**
 * Generate an enriched entity
 *
 * Example:
 * const EnrichedEntityBuilder = require('../../common/builder/enriched-entity.js');
 * const enrichedEntity = (new EnrichedEntityBuilder()).withIdentifier('designer').build();
 */

class EnrichedEntityBuilder {
  constructor() {
    this.enrichedEntity = {
      identifier: '',
      labels: {},
    };
  }

  withIdentifier(identifier) {
    this.enrichedEntity.identifier = identifier;

    return this;
  }

  withLabels(labels) {
    this.enrichedEntity.labels = labels;

    return this;
  }

  withImage(image) {
    this.enrichedEntity.image = image;

    return this;
  }

  build() {
    return this.enrichedEntity;
  }
}

module.exports = EnrichedEntityBuilder;
