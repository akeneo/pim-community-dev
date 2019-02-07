import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ReferenceEntityIdentifier, {
  createIdentifier as createReferenceEntityIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {
  NormalizedAttribute,
  Attribute,
  ConcreteAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {RecordType, NormalizedRecordType} from 'akeneoreferenceentity/domain/model/attribute/type/record/record-type';

export interface NormalizedRecordCollectionAttribute extends NormalizedAttribute {
  type: 'record_collection';
  record_type: NormalizedRecordType;
}

export type NormalizedRecordAdditionalProperty = NormalizedRecordType;

export type RecordAdditionalProperty = RecordType;

export interface RecordCollectionAttribute extends Attribute {
  recordType: RecordType;
  normalize(): NormalizedRecordCollectionAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteRecordCollectionAttribute extends ConcreteAttribute implements RecordCollectionAttribute {
  private constructor(
    identifier: Identifier,
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly recordType: RecordType
  ) {
    super(
      identifier,
      referenceEntityIdentifier,
      code,
      labelCollection,
      'record_collection',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    if (!(recordType instanceof RecordType)) {
      throw new InvalidArgumentError('Attribute expects a RecordType as recordType');
    }

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedRecordCollectionAttribute: NormalizedRecordCollectionAttribute) {
    return new ConcreteRecordCollectionAttribute(
      createIdentifier(normalizedRecordCollectionAttribute.identifier),
      createReferenceEntityIdentifier(normalizedRecordCollectionAttribute.reference_entity_identifier),
      createCode(normalizedRecordCollectionAttribute.code),
      createLabelCollection(normalizedRecordCollectionAttribute.labels),
      normalizedRecordCollectionAttribute.value_per_locale,
      normalizedRecordCollectionAttribute.value_per_channel,
      normalizedRecordCollectionAttribute.order,
      normalizedRecordCollectionAttribute.is_required,
      RecordType.createFromNormalized(normalizedRecordCollectionAttribute.record_type)
    );
  }

  public normalize(): NormalizedRecordCollectionAttribute {
    return {
      ...super.normalize(),
      type: 'record_collection',
      record_type: this.recordType.normalize(),
    };
  }
}

export const denormalize = ConcreteRecordCollectionAttribute.createFromNormalized;
