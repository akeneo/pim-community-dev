import {
  createEnrichedEntity,
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import File, {createEmptyFile} from 'akeneoenrichedentity/domain/model/file';

const michelIdentifier = createIdentifier('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierIdentifier = createIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > enriched entity > domain > model --- enriched entity', () => {
  test('I can create a new enriched entity with a identifier and labels', () => {
    expect(createEnrichedEntity(michelIdentifier, michelLabels, createEmptyFile()).getIdentifier()).toBe(
      michelIdentifier
    );
  });

  test('I cannot create a malformed enriched entity', () => {
    expect(() => {
      createEnrichedEntity(michelIdentifier);
    }).toThrow('EnrichedEntity expect a LabelCollection as labelCollection argument');
    expect(() => {
      createEnrichedEntity();
    }).toThrow('EnrichedEntity expect an EnrichedEntityIdentifier as identifier argument');
    expect(() => {
      createEnrichedEntity(12);
    }).toThrow('EnrichedEntity expect an EnrichedEntityIdentifier as identifier argument');
    expect(() => {
      createEnrichedEntity(michelIdentifier, 52);
    }).toThrow('EnrichedEntity expect a LabelCollection as labelCollection argument');
    expect(() => {
      createEnrichedEntity(michelIdentifier, 52, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('EnrichedEntity expect a LabelCollection as labelCollection argument');
    expect(() => {
      createEnrichedEntity(michelIdentifier, michelLabels, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('EnrichedEntity expect a File as image argument');
  });

  test('I can compare two enriched entities', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createEnrichedEntity(didierIdentifier, didierLabels, createEmptyFile()).equals(
        createEnrichedEntity(didierIdentifier, didierLabels, createEmptyFile())
      )
    ).toBe(true);
    expect(
      createEnrichedEntity(didierIdentifier, didierLabels, createEmptyFile()).equals(
        createEnrichedEntity(michelIdentifier, michelLabels, createEmptyFile())
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(createEnrichedEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabel('en_US')).toBe('Michel');
    expect(createEnrichedEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR')).toBe('[michel]');
    expect(createEnrichedEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR', false)).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(createEnrichedEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabelCollection()).toBe(
      michelLabels
    );
  });

  test('I can normalize an enriched entity', () => {
    const michelEnrichedEntity = createEnrichedEntity(michelIdentifier, michelLabels, createEmptyFile());

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
      image: null,
    });

    expect(michelEnrichedEntity.normalize()).toEqual({
      identifier: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });
});
