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
import {IsDecimal, NormalizedIsDecimal} from 'akeneoreferenceentity/domain/model/attribute/type/number/is-decimal';
import {MinValue, NormalizedMinValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/min-value';
import {MaxValue, NormalizedMaxValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/max-value';

export type NumberAdditionalProperty = IsDecimal | MinValue | MaxValue;
export type NormalizedNumberAdditionalProperty = NormalizedIsDecimal | NormalizedMinValue | NormalizedMaxValue;

export interface NormalizedNumberAttribute extends NormalizedAttribute {
  type: 'number';
  is_decimal: NormalizedIsDecimal;
  min_value: NormalizedMinValue;
  max_value: NormalizedMaxValue;
}

export interface NumberAttribute extends Attribute {
  isDecimal: IsDecimal;
  minValue: MinValue;
  maxValue: MaxValue;
  normalize(): NormalizedNumberAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteNumberAttribute extends ConcreteAttribute implements NumberAttribute {
  private constructor(
    identifier: Identifier,
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly isDecimal: IsDecimal,
    readonly minValue: MinValue,
    readonly maxValue: MaxValue
  ) {
    super(
      identifier,
      referenceEntityIdentifier,
      code,
      labelCollection,
      'number',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    if (!(isDecimal instanceof IsDecimal)) {
      throw new Error('Attribute expects a IsDecimal as isDecimal');
    }

    if (!(minValue instanceof MinValue)) {
      throw new Error('Attribute expects a MinValue as minValue');
    }

    if (!(maxValue instanceof MaxValue)) {
      throw new Error('Attribute expects a MaxValue as maxValue');
    }

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedNumberAttribute: NormalizedNumberAttribute) {
    return new ConcreteNumberAttribute(
      createIdentifier(normalizedNumberAttribute.identifier),
      createReferenceEntityIdentifier(normalizedNumberAttribute.reference_entity_identifier),
      createCode(normalizedNumberAttribute.code),
      createLabelCollection(normalizedNumberAttribute.labels),
      normalizedNumberAttribute.value_per_locale,
      normalizedNumberAttribute.value_per_channel,
      normalizedNumberAttribute.order,
      normalizedNumberAttribute.is_required,
      new IsDecimal(normalizedNumberAttribute.is_decimal),
      new MinValue(normalizedNumberAttribute.min_value),
      new MaxValue(normalizedNumberAttribute.max_value)
    );
  }

  public normalize(): NormalizedNumberAttribute {
    return {
      ...super.normalize(),
      type: 'number',
      is_decimal: this.isDecimal.normalize(),
      min_value: this.minValue.normalize(),
      max_value: this.maxValue.normalize(),
    };
  }
}

export const denormalize = ConcreteNumberAttribute.createFromNormalized;
