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

export interface NormalizedRecordAttribute extends NormalizedAttribute {
  type: 'record';
  record_type: NormalizedRecordType;
}

export type NormalizedRecordAdditionalProperty = NormalizedRecordType;

export type RecordAdditionalProperty = RecordType;

export interface RecordAttribute extends Attribute {
  recordType: RecordType;
  normalize(): NormalizedRecordAttribute;
  getRecordType(): RecordType;
}

export class InvalidArgumentError extends Error {}

export class ConcreteRecordAttribute extends ConcreteAttribute implements RecordAttribute {
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
      'record',
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

  public static createFromNormalized(normalizedRecordAttribute: NormalizedRecordAttribute) {
    return new ConcreteRecordAttribute(
      createIdentifier(normalizedRecordAttribute.identifier),
      createReferenceEntityIdentifier(normalizedRecordAttribute.reference_entity_identifier),
      createCode(normalizedRecordAttribute.code),
      createLabelCollection(normalizedRecordAttribute.labels),
      normalizedRecordAttribute.value_per_locale,
      normalizedRecordAttribute.value_per_channel,
      normalizedRecordAttribute.order,
      normalizedRecordAttribute.is_required,
      RecordType.createFromNormalized(normalizedRecordAttribute.record_type)
    );
  }

  getRecordType(): RecordType {
    return this.recordType;
  }

  public normalize(): NormalizedRecordAttribute {
    return {
      ...super.normalize(),
      type: 'record',
      record_type: this.recordType.normalize(),
    };
  }
}

export const denormalize = ConcreteRecordAttribute.createFromNormalized;
