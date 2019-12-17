import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {NormalizedAttribute, Attribute, ConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MinValue, NormalizedMinValue} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';
import {MaxValue, NormalizedMaxValue} from 'akeneoassetmanager/domain/model/attribute/type/number/max-value';

export type DecimalsAllowed = boolean;
export type NumberAdditionalProperty = DecimalsAllowed | MinValue | MaxValue;
export type NormalizedNumberAdditionalProperty = DecimalsAllowed | NormalizedMinValue | NormalizedMaxValue;

export const NUMBER_ATTRIBUTE_TYPE = 'number';

export interface NormalizedNumberAttribute extends NormalizedAttribute {
  type: typeof NUMBER_ATTRIBUTE_TYPE;
  decimals_allowed: DecimalsAllowed;
  min_value: NormalizedMinValue;
  max_value: NormalizedMaxValue;
}

export const isNumberAttribute = (numberAttribute: NormalizedAttribute): numberAttribute is NormalizedNumberAttribute =>
  NUMBER_ATTRIBUTE_TYPE === numberAttribute.type;

export interface NumberAttribute extends Attribute {
  decimalsAllowed: DecimalsAllowed;
  minValue: MinValue;
  maxValue: MaxValue;
  normalize(): NormalizedNumberAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteNumberAttribute extends ConcreteAttribute implements NumberAttribute {
  private constructor(
    identifier: AttributeIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly decimalsAllowed: DecimalsAllowed,
    readonly minValue: MinValue,
    readonly maxValue: MaxValue
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      'number',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedNumberAttribute: NormalizedNumberAttribute) {
    return new ConcreteNumberAttribute(
      denormalizeAttributeIdentifier(normalizedNumberAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedNumberAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedNumberAttribute.code),
      denormalizeLabelCollection(normalizedNumberAttribute.labels),
      normalizedNumberAttribute.value_per_locale,
      normalizedNumberAttribute.value_per_channel,
      normalizedNumberAttribute.order,
      normalizedNumberAttribute.is_required,
      normalizedNumberAttribute.decimals_allowed,
      normalizedNumberAttribute.min_value,
      normalizedNumberAttribute.max_value
    );
  }

  public normalize(): NormalizedNumberAttribute {
    return {
      ...super.normalize(),
      type: 'number',
      decimals_allowed: this.decimalsAllowed,
      min_value: this.minValue,
      max_value: this.maxValue,
    };
  }
}

export const denormalize = ConcreteNumberAttribute.createFromNormalized;
