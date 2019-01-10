import {
  createReferenceEntity,
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import File, {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';

const michelIdentifier = createIdentifier('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierIdentifier = createIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > reference entity > domain > model --- reference entity', () => {
  test('I can create a new reference entity with an identifier and labels', () => {
    expect(createReferenceEntity(michelIdentifier, michelLabels, createEmptyFile()).getIdentifier()).toBe(
      michelIdentifier
    );
  });

  test('I cannot create a malformed reference entity', () => {
    expect(() => {
      createReferenceEntity(michelIdentifier);
    }).toThrow('ReferenceEntity expect a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntity();
    }).toThrow('ReferenceEntity expect an ReferenceEntityIdentifier as identifier argument');
    expect(() => {
      createReferenceEntity(12);
    }).toThrow('ReferenceEntity expect an ReferenceEntityIdentifier as identifier argument');
    expect(() => {
      createReferenceEntity(michelIdentifier, 52);
    }).toThrow('ReferenceEntity expect a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntity(michelIdentifier, 52, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('ReferenceEntity expect a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntity(michelIdentifier, michelLabels, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('ReferenceEntity expect a File as image argument');
  });

  test('I can compare two reference entities', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createReferenceEntity(didierIdentifier, didierLabels, createEmptyFile()).equals(
        createReferenceEntity(didierIdentifier, didierLabels, createEmptyFile())
      )
    ).toBe(true);
    expect(
      createReferenceEntity(didierIdentifier, didierLabels, createEmptyFile()).equals(
        createReferenceEntity(michelIdentifier, michelLabels, createEmptyFile())
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(createReferenceEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabel('en_US')).toBe('Michel');
    expect(createReferenceEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR')).toBe('[michel]');
    expect(createReferenceEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR', false)).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(createReferenceEntity(michelIdentifier, michelLabels, createEmptyFile()).getLabelCollection()).toBe(
      michelLabels
    );
  });

  test('I can normalize a reference entity', () => {
    const michelReferenceEntity = createReferenceEntity(michelIdentifier, michelLabels, createEmptyFile());

    expect(michelReferenceEntity.normalize()).toEqual({
      identifier: 'michel',
      code: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });

  test('I can normalize a reference entity', () => {
    const michelReferenceEntity = denormalizeReferenceEntity({
      identifier: 'michel',
      labels: {
        en_US: 'Michel',
      },
      image: null,
    });

    expect(michelReferenceEntity.normalize()).toEqual({
      identifier: 'michel',
      code: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });
});
