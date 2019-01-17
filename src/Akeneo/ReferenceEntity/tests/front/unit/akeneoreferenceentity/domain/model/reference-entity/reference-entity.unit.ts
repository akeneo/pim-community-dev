import {
  createReferenceEntity,
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';
import {createAttributeReference} from 'akeneoreferenceentity/domain/model/attribute/attribute-reference';

const michelIdentifier = createIdentifier('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierIdentifier = createIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});
const attributeAsImage = createAttributeReference(null);
const attributeAsLabel = createAttributeReference(null);

describe('akeneo > reference entity > domain > model --- reference entity', () => {
  test('I can create a new reference entity with an identifier and labels', () => {
    expect(
      createReferenceEntity(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        attributeAsLabel,
        attributeAsImage
      ).getIdentifier()
    ).toBe(michelIdentifier);
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
      createReferenceEntity(
        didierIdentifier,
        didierLabels,
        createEmptyFile(),
        createAttributeReference('name_michel_fingerprint'),
        createAttributeReference('image_michel_fingerprint')
      ).equals(
        createReferenceEntity(
          didierIdentifier,
          didierLabels,
          createEmptyFile(),
          createAttributeReference('name_michel_fingerprint'),
          createAttributeReference('image_michel_fingerprint')
        )
      )
    ).toBe(true);
    expect(
      createReferenceEntity(
        didierIdentifier,
        didierLabels,
        createEmptyFile(),
        createAttributeReference('name_michel_fingerprint'),
        createAttributeReference('image_michel_fingerprint')
      ).equals(
        createReferenceEntity(
          michelIdentifier,
          michelLabels,
          createEmptyFile(),
          createAttributeReference('name_michel_fingerprint'),
          createAttributeReference('image_michel_fingerprint')
        )
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(
      createReferenceEntity(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        createAttributeReference('name_michel_fingerprint'),
        createAttributeReference('image_michel_fingerprint')
      ).getLabel('en_US')
    ).toBe('Michel');
    expect(
      createReferenceEntity(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        createAttributeReference('name_michel_fingerprint'),
        createAttributeReference('image_michel_fingerprint')
      ).getLabel('fr_FR')
    ).toBe('[michel]');
    expect(
      createReferenceEntity(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        createAttributeReference('name_michel_fingerprint'),
        createAttributeReference('image_michel_fingerprint')
      ).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(
      createReferenceEntity(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        createAttributeReference('name_michel_fingerprint'),
        createAttributeReference('image_michel_fingerprint')
      ).getLabelCollection()
    ).toBe(michelLabels);
  });

  test('I can normalize a reference entity', () => {
    const michelReferenceEntity = createReferenceEntity(
      michelIdentifier,
      michelLabels,
      createEmptyFile(),
      attributeAsImage,
      attributeAsLabel
    );

    expect(michelReferenceEntity.normalize()).toEqual({
      identifier: 'michel',
      code: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
      attribute_as_image: null,
      attribute_as_label: null,
    });
  });

  test('I can normalize a reference entity', () => {
    const michelReferenceEntity = denormalizeReferenceEntity({
      identifier: 'michel',
      labels: {
        en_US: 'Michel',
      },
      image: null,
      attribute_as_image: null,
      attribute_as_label: null,
    });

    expect(michelReferenceEntity.normalize()).toEqual({
      identifier: 'michel',
      code: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
      attribute_as_image: null,
      attribute_as_label: null,
    });
  });
});
