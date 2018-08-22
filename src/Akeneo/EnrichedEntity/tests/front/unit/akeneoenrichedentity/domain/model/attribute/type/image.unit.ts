import {ConcreteImageAttribute} from 'akeneoenrichedentity/domain/model/attribute/type/image';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import {MaxFileSize} from 'akeneoenrichedentity/domain/model/attribute/type/image/max-file-size';

const normalizedFrontView = {
  identifier: {identifier: 'front_view', enriched_entity_identifier: 'designer'},
  enriched_entity_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'image',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  required: true,
  max_file_size: null,
  allowed_extensions: [],
};

describe('akeneo > attribute > domain > model > attribute > type --- ImageAttribute', () => {
  test('I can create a ConcreteImageAttribute from normalized', () => {
    expect(ConcreteImageAttribute.createFromNormalized(normalizedFrontView).normalize()).toEqual(normalizedFrontView);
  });
  test('I cannot create an invalid ConcreteImageAttribute', () => {
    expect(() => {
      new ConcreteImageAttribute(
        createIdentifier('designer', 'front_view'),
        createEnrichedEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expect a MaxFileSize as maxFileSize');

    expect(() => {
      new ConcreteImageAttribute(
        createIdentifier('designer', 'front_view'),
        createEnrichedEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0,
        true,
        MaxFileSize.createFromNormalized('12.4')
      );
    }).toThrow('Attribute expect a AllowedExtension as allowedExtension');
  });
});
