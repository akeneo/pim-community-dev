import {
  createEnrichedEntity,
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

const michelIdentifier = createIdentifier('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierIdentifier = createIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > enriched entity > domain > model --- enriched entity', () => {
  test('I can create a new enriched entity with a identifier and labels', () => {
    expect(createEnrichedEntity(michelIdentifier, michelLabels).getIdentifier()).toBe(michelIdentifier);
  });

  test('I cannot create a malformed enriched entity', () => {
    expect(() => {
      createEnrichedEntity(michelIdentifier);
    }).toThrow('EnrichedEntity expect a LabelCollection as second argument');
    expect(() => {
      createEnrichedEntity();
    }).toThrow('EnrichedEntity expect an EnrichedEntityIdentifier as first argument');
    expect(() => {
      createEnrichedEntity(12);
    }).toThrow('EnrichedEntity expect an EnrichedEntityIdentifier as first argument');
    expect(() => {
      createEnrichedEntity(michelIdentifier, 52);
    }).toThrow('EnrichedEntity expect a LabelCollection as second argument');
  });

  test('I can compare two enriched entities', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createEnrichedEntity(didierIdentifier, didierLabels).equals(createEnrichedEntity(didierIdentifier, didierLabels))
    ).toBe(true);
    expect(
      createEnrichedEntity(didierIdentifier, didierLabels).equals(createEnrichedEntity(michelIdentifier, michelLabels))
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(createEnrichedEntity(michelIdentifier, michelLabels).getLabel('en_US')).toBe('Michel');
    expect(createEnrichedEntity(michelIdentifier, michelLabels).getLabel('fr_FR')).toBe('[michel]');
  });

  test('I can get the collection of labels', () => {
    expect(createEnrichedEntity(michelIdentifier, michelLabels).getLabelCollection()).toBe(michelLabels);
  });

  test('I can normalize an enriched entity', () => {
    const michelEnrichedEntity = createEnrichedEntity(michelIdentifier, michelLabels);

    expect(michelEnrichedEntity.normalize()).toEqual({
      identifier: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });

  test('I can normalize an enriched entity', () => {
    const michelEnrichedEntity = denormalizeEnrichedEntity({
      identifier: 'michel',
      labels: {
        en_US: 'Michel',
      },
    });

    expect(michelEnrichedEntity.normalize()).toEqual({
      identifier: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });
});
