import {ConcreteRecordCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record-collection';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {RecordType} from 'akeneoreferenceentity/domain/model/attribute/type/record/record-type';

const normalizedBrand = {
  identifier: 'brand',
  reference_entity_identifier: 'designer',
  code: 'brand',
  labels: {en_US: 'Brand'},
  type: 'record_collection',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  record_type: 'brand',
};

describe('akeneo > attribute > domain > model > attribute > type --- RecordCollectionAttribute', () => {
  test('I can create a ConcreteRecordCollectionAttribute from normalized', () => {
    expect(ConcreteRecordCollectionAttribute.createFromNormalized(normalizedBrand).normalize()).toEqual(
      normalizedBrand
    );
  });
  test('I can create get a record type', () => {
    expect(ConcreteRecordCollectionAttribute.createFromNormalized(normalizedBrand).getRecordType()).toEqual(
      RecordType.createFromString('brand')
    );
  });
  test('I cannot create an invalid ConcreteRecordCollectionAttribute', () => {
    expect(() => {
      new ConcreteRecordCollectionAttribute(
        createIdentifier('designer', 'brand'),
        createReferenceEntityIdentifier('designer'),
        createCode('brand'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expects a RecordType as recordType');
  });
});
