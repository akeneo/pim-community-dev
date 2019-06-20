import {
  createReferenceEntityListItem,
  denormalizeReferenceEntityListItem,
  createEmptyReferenceEntityListItem,
} from 'akeneoreferenceentity/domain/model/reference-entity/list';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';

const michelIdentifier = createIdentifier('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierCode = createIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > reference entity > domain > model --- reference entity', () => {
  test('I can create a new reference entity with an identifier and labels', () => {
    expect(createReferenceEntityListItem(michelIdentifier, michelLabels, createEmptyFile()).getIdentifier()).toBe(
      michelIdentifier
    );
  });

  test('I cannot create a malformed reference entity', () => {
    expect(() => {
      createReferenceEntityListItem(michelIdentifier);
    }).toThrow('ReferenceEntityListItem expects a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntityListItem();
    }).toThrow('ReferenceEntityListItem expects an Identifier as identifier argument');
    expect(() => {
      createReferenceEntityListItem(12);
    }).toThrow('ReferenceEntityListItem expects an Identifier as identifier argument');
    expect(() => {
      createReferenceEntityListItem(michelIdentifier, 52);
    }).toThrow('ReferenceEntityListItem expects a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntityListItem(michelIdentifier, 52, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('ReferenceEntityListItem expects a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntityListItem(michelIdentifier, michelLabels, {
        filePath: 'my_path.png',
        originalFilename: 'path.png',
      });
    }).toThrow('ReferenceEntityListItem expects a File as image argument');
  });

  test('I can compare two reference entities', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createReferenceEntityListItem(didierCode, didierLabels, createEmptyFile()).equals(
        createReferenceEntityListItem(didierCode, didierLabels, createEmptyFile())
      )
    ).toBe(true);
    expect(
      createReferenceEntityListItem(didierCode, didierLabels, createEmptyFile()).equals(
        createReferenceEntityListItem(michelIdentifier, michelLabels, createEmptyFile())
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(createReferenceEntityListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabel('en_US')).toBe(
      'Michel'
    );
    expect(createReferenceEntityListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR')).toBe(
      '[michel]'
    );
    expect(
      createReferenceEntityListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(createReferenceEntityListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabelCollection()).toBe(
      michelLabels
    );
  });

  test('I can create an empty reference entity creation', () => {
    expect(createEmptyReferenceEntityListItem()).toEqual(
      denormalizeReferenceEntityListItem({identifier: '', labels: {}, image: null})
    );
  });

  test('I can normalize a reference entity', () => {
    const michelReferenceEntity = createReferenceEntityListItem(michelIdentifier, michelLabels, createEmptyFile());

    expect(michelReferenceEntity.normalize()).toEqual({
      identifier: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });

  test('I can normalize a reference entity', () => {
    const michelReferenceEntity = denormalizeReferenceEntityListItem({
      identifier: 'michel',
      labels: {
        en_US: 'Michel',
      },
      image: null,
    });

    expect(michelReferenceEntity.normalize()).toEqual({
      identifier: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });
});
