import {
  createReferenceEntityCreation,
  createEmptyReferenceEntityCreation,
  denormalizeReferenceEntityCreation,
} from 'akeneoreferenceentity/domain/model/reference-entity/creation';
import {createCode} from 'akeneoreferenceentity/domain/model/code';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';

const michelCode = createCode('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierCode = createCode('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > reference entity > domain > model --- reference entity', () => {
  test('I can create a new reference entity creation with an identifier and labels', () => {
    expect(createReferenceEntityCreation(michelCode, michelLabels).getCode()).toBe(michelCode);
  });

  test('I cannot create a malformed reference entity creation', () => {
    expect(() => {
      createReferenceEntityCreation(michelCode);
    }).toThrow('ReferenceEntityCreation expects a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntityCreation();
    }).toThrow('ReferenceEntityCreation expects a Code as code argument');
    expect(() => {
      createReferenceEntityCreation(12);
    }).toThrow('ReferenceEntityCreation expects a Code as code argument');
    expect(() => {
      createReferenceEntityCreation(michelCode, 52);
    }).toThrow('ReferenceEntityCreation expects a LabelCollection as labelCollection argument');
    expect(() => {
      createReferenceEntityCreation(michelCode, 52, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('ReferenceEntityCreation expects a LabelCollection as labelCollection argument');
  });

  test('I can compare two reference entities', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createReferenceEntityCreation(didierCode, didierLabels).equals(
        createReferenceEntityCreation(didierCode, didierLabels)
      )
    ).toBe(true);
    expect(
      createReferenceEntityCreation(didierCode, didierLabels).equals(
        createReferenceEntityCreation(michelCode, michelLabels)
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(createReferenceEntityCreation(michelCode, michelLabels).getLabel('en_US')).toBe('Michel');
    expect(createReferenceEntityCreation(michelCode, michelLabels).getLabel('fr_FR')).toBe('[michel]');
    expect(createReferenceEntityCreation(michelCode, michelLabels).getLabel('fr_FR', false)).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(createReferenceEntityCreation(michelCode, michelLabels).getLabelCollection()).toBe(michelLabels);
  });

  test('I can create an empty reference entity creation', () => {
    expect(createEmptyReferenceEntityCreation()).toEqual(denormalizeReferenceEntityCreation({code: '', labels: {}}));
  });

  test('I can normalize a reference entity creation', () => {
    const michelReferenceEntity = createReferenceEntityCreation(michelCode, michelLabels);

    expect(michelReferenceEntity.normalize()).toEqual({
      code: 'michel',
      labels: {en_US: 'Michel'},
    });
  });

  test('I can normalize a reference entity creation', () => {
    const michelReferenceEntity = denormalizeReferenceEntityCreation({
      code: 'michel',
      labels: {
        en_US: 'Michel',
      },
    });

    expect(michelReferenceEntity.normalize()).toEqual({
      code: 'michel',
      labels: {en_US: 'Michel'},
    });
  });
});
