import {
  enrichedEntityEditionReceived,
  enrichedEntityEditionUpdated,
  enrichedEntityEditionLabelUpdated,
  enrichedEntityEditionSubmission,
  enrichedEntityEditionSucceeded,
  enrichedEntityEditionErrorOccured,
  enrichedEntityEditionImageUpdated,
} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';

describe('akeneo > enriched entity > domain > event > enriched entity --- edit', () => {
  test('I can create a enrichedEntityEditionReceived event', () => {
    const normalizedEnrichedEntity = {
      identifier: 'designer',
      labels: {
        en_US: 'Designer',
      },
    };
    expect(enrichedEntityEditionReceived(normalizedEnrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_RECEIVED',
      enrichedEntity: normalizedEnrichedEntity,
    });
  });

  test('I can create a enrichedEntityEditionUpdated event', () => {
    const normalizedEnrichedEntity = {
      identifier: 'designer',
      labels: {
        en_US: 'Designer',
      },
    };
    expect(enrichedEntityEditionUpdated(normalizedEnrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_UPDATED',
      enrichedEntity: normalizedEnrichedEntity,
    });
  });

  test('I can create a enrichedEntityEditionUpdated event', () => {
    const normalizedEnrichedEntity = {
      identifier: 'designer',
      labels: {
        en_US: 'Designer',
      },
    };
    expect(enrichedEntityEditionUpdated(normalizedEnrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_UPDATED',
      enrichedEntity: normalizedEnrichedEntity,
    });
  });

  test('I can create a enrichedEntityEditionLabelUpdated event', () => {
    expect(enrichedEntityEditionLabelUpdated('Designer', 'en_US')).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED',
      value: 'Designer',
      locale: 'en_US',
    });
  });

  test('I can create a enrichedEntityEditionImageUpdated event', () => {
    expect(enrichedEntityEditionImageUpdated({my: 'image'})).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_IMAGE_UPDATED',
      image: {my: 'image'},
    });
  });

  test('I can create a enrichedEntityEditionSubmission event', () => {
    expect(enrichedEntityEditionSubmission()).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_SUBMISSION',
    });
  });

  test('I can create a enrichedEntityEditionSucceeded event', () => {
    expect(enrichedEntityEditionSucceeded()).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_SUCCEEDED',
    });
  });

  test('I can create a enrichedEntityEditionErrorOccured event', () => {
    expect(enrichedEntityEditionErrorOccured([{my: 'error'}])).toEqual({
      type: 'ENRICHED_ENTITY_EDITION_ERROR_OCCURED',
      errors: [{my: 'error'}],
    });
  });
});
