import Identifier, {createIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  createIdentifier as createAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {
  NormalizedAttribute,
  Attribute,
  ConcreteAttribute,
} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {DecimalsAllowed, NormalizedDecimalsAllowed} from 'akeneoassetmanager/domain/model/attribute/type/number/decimals-allowed';
import {MinValue, NormalizedMinValue} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';
import {MaxValue, NormalizedMaxValue} from 'akeneoassetmanager/domain/model/attribute/type/number/max-value';

export type NumberAdditionalProperty = DecimalsAllowed | MinValue | MaxValue;
export type NormalizedNumberAdditionalProperty = NormalizedDecimalsAllowed | NormalizedMinValue | NormalizedMaxValue;

export interface NormalizedNumberAttribute extends NormalizedAttribute {
  type: 'number';
  decimals_allowed: NormalizedDecimalsAllowed;
  min_value: NormalizedMinValue;
  max_value: NormalizedMaxValue;
}

export interface NumberAttribute extends Attribute {
  decimalsAllowed: DecimalsAllowed;
  minValue: MinValue;
  maxValue: MaxValue;
  normalize(): NormalizedNumberAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteNumberAttribute extends ConcreteAttribute implements NumberAttribute {
  private constructor(
    identifier: Identifier,
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

    if (!(decimalsAllowed instanceof DecimalsAllowed)) {
      throw new Error('Attribute expects a DecimalsAllowed as decimalsAllowed');
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
      createAssetFamilyIdentifier(normalizedNumberAttribute.asset_family_identifier),
      createCode(normalizedNumberAttribute.code),
      createLabelCollection(normalizedNumberAttribute.labels),
      normalizedNumberAttribute.value_per_locale,
      normalizedNumberAttribute.value_per_channel,
      normalizedNumberAttribute.order,
      normalizedNumberAttribute.is_required,
      new DecimalsAllowed(normalizedNumberAttribute.decimals_allowed),
      new MinValue(normalizedNumberAttribute.min_value),
      new MaxValue(normalizedNumberAttribute.max_value)
    );
  }

  public normalize(): NormalizedNumberAttribute {
    return {
      ...super.normalize(),
      type: 'number',
      decimals_allowed: this.decimalsAllowed.normalize(),
      min_value: this.minValue.normalize(),
      max_value: this.maxValue.normalize(),
    };
  }
}

export const denormalize = ConcreteNumberAttribute.createFromNormalized;
