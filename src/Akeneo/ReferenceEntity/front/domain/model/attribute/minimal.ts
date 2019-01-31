import ReferenceEntityIdentifier, {
  createIdentifier as createReferenceEntityIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoreferenceentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {RecordType, NormalizedRecordType} from 'akeneoreferenceentity/domain/model/attribute/type/record/record-type';

export interface MinimalNormalizedAttribute {
  reference_entity_identifier: string;
  type: string;
  code: string;
  labels: NormalizedLabelCollection;
  value_per_locale: boolean;
  value_per_channel: boolean;
}

export default interface MinimalAttribute {
  referenceEntityIdentifier: ReferenceEntityIdentifier;
  code: AttributeCode;
  labelCollection: LabelCollection;
  type: string;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
  getCode: () => AttributeCode;
  getReferenceEntityIdentifier: () => ReferenceEntityIdentifier;
  getType(): string;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  normalize(): MinimalNormalizedAttribute;
}

class InvalidArgumentError extends Error {}

export const isRecordAttributeType = (attributeType: string) => {
  return ['record', 'record_collection'].includes(attributeType);
};

export class MinimalConcreteAttribute implements MinimalAttribute {
  protected constructor(
    readonly referenceEntityIdentifier: ReferenceEntityIdentifier,
    readonly code: AttributeCode,
    readonly labelCollection: LabelCollection,
    readonly type: string,
    readonly valuePerLocale: boolean,
    readonly valuePerChannel: boolean
  ) {
    if (!(referenceEntityIdentifier instanceof ReferenceEntityIdentifier)) {
      throw new InvalidArgumentError('Attribute expects an ReferenceEntityIdentifier argument');
    }
    if (!(code instanceof AttributeCode)) {
      throw new InvalidArgumentError('Attribute expects a AttributeCode argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Attribute expects a LabelCollection argument');
    }
    if (typeof type !== 'string') {
      throw new InvalidArgumentError('Attribute expects a string as attribute type');
    }
    if (typeof valuePerLocale !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as valuePerLocale');
    }
    if (typeof valuePerChannel !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as valuePerChannel');
    }
  }

  public static createFromNormalized(minimalNormalizedAttribute: MinimalNormalizedAttribute) {
    return new MinimalConcreteAttribute(
      createReferenceEntityIdentifier(minimalNormalizedAttribute.reference_entity_identifier),
      createCode(minimalNormalizedAttribute.code),
      createLabelCollection(minimalNormalizedAttribute.labels),
      minimalNormalizedAttribute.type,
      minimalNormalizedAttribute.value_per_locale,
      minimalNormalizedAttribute.value_per_channel
    );
  }

  public getReferenceEntityIdentifier(): ReferenceEntityIdentifier {
    return this.referenceEntityIdentifier;
  }

  public getCode(): AttributeCode {
    return this.code;
  }

  public getType(): string {
    return this.type;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getCode().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public normalize(): MinimalNormalizedAttribute {
    return {
      reference_entity_identifier: this.referenceEntityIdentifier.stringValue(),
      code: this.code.stringValue(),
      type: this.getType(),
      labels: this.labelCollection.normalize(),
      value_per_locale: this.valuePerLocale,
      value_per_channel: this.valuePerChannel,
    };
  }
}

export interface MinimalRecordNormalizedAttribute extends MinimalNormalizedAttribute {
  record_type: NormalizedRecordType;
}

export class MinimalRecordConcreteAttribute extends MinimalConcreteAttribute {
  protected constructor(
    readonly referenceEntityIdentifier: ReferenceEntityIdentifier,
    readonly code: AttributeCode,
    readonly labelCollection: LabelCollection,
    readonly type: string,
    readonly valuePerLocale: boolean,
    readonly valuePerChannel: boolean,
    readonly recordType: RecordType
  ) {
    super(referenceEntityIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

    if (!isRecordAttributeType(type)) {
      throw new InvalidArgumentError('MinimalRecordAttribute type needs to be "record" or "record_collection"');
    }

    if (!(recordType instanceof RecordType)) {
      throw new InvalidArgumentError('Attribute expects a RecordType argument');
    }
  }

  public static createFromNormalized(minimalNormalizedAttribute: MinimalRecordNormalizedAttribute) {
    return new MinimalRecordConcreteAttribute(
      createReferenceEntityIdentifier(minimalNormalizedAttribute.reference_entity_identifier),
      createCode(minimalNormalizedAttribute.code),
      createLabelCollection(minimalNormalizedAttribute.labels),
      minimalNormalizedAttribute.type,
      minimalNormalizedAttribute.value_per_locale,
      minimalNormalizedAttribute.value_per_channel,
      RecordType.createFromNormalized(minimalNormalizedAttribute.record_type)
    );
  }

  public normalize(): MinimalRecordNormalizedAttribute {
    return {
      ...super.normalize(),
      record_type: this.recordType.normalize(),
    };
  }
}

export const denormalizeMinimalAttribute = (normalizedAttribute: MinimalNormalizedAttribute) => {
  if (isRecordAttributeType(normalizedAttribute.type)) {
    return MinimalRecordConcreteAttribute.createFromNormalized(normalizedAttribute as MinimalRecordNormalizedAttribute);
  }

  return MinimalConcreteAttribute.createFromNormalized(normalizedAttribute);
};
