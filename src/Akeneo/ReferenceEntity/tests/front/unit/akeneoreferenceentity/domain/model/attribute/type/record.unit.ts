import {ConcreteRecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';
import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ReferenceEntityIdentifier, {
  createIdentifier as createReferenceEntityIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';
import {RecordType} from 'akeneoreferenceentity/domain/model/attribute/type/record/record-type';

const normalizedBrand = {
  identifier: 'brand',
  reference_entity_identifier: 'designer',
  code: 'brand',
  labels: {en_US: 'Brand'},
  type: 'record',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  record_type: 'brand',
};

describe('akeneo > attribute > domain > model > attribute > type --- RecordAttribute', () => {
  test('I can create a ConcreteRecordAttribute from normalized', () => {
    expect(ConcreteRecordAttribute.createFromNormalized(normalizedBrand).normalize()).toEqual(normalizedBrand);
  });
  test('I can create get a record type', () => {
    expect(ConcreteRecordAttribute.createFromNormalized(normalizedBrand).getRecordType()).toEqual(
      RecordType.createFromString('brand')
    );
  });
  test('I cannot create an invalid ConcreteRecordAttribute', () => {
    expect(() => {
      new ConcreteRecordAttribute(
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
