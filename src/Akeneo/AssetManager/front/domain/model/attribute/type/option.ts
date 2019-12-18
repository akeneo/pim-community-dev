import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {Attribute, ConcreteAttribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {Option, createOptionFromNormalized} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import OptionCode, {optionCodesAreEqual} from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';

export const OPTION_ATTRIBUTE_TYPE = 'option';

export const OPTION_ATTRIBUTE_TYPE = 'option';

export interface NormalizedOptionAttribute extends NormalizedAttribute {
  type: typeof OPTION_ATTRIBUTE_TYPE;
  options: Option[];
}

export const isOptionAttribute = (optionAttribute: NormalizedAttribute): optionAttribute is NormalizedOptionAttribute =>
  optionAttribute.type === OPTION_ATTRIBUTE_TYPE;

export type NormalizedOptionAdditionalProperty = Option;
export type OptionAdditionalProperty = Option;
export interface AttributeWithOptions {
  options: Option[];
  getOptions(): Option[];
  setOptions(options: Option[]): AttributeWithOptions;
}

export interface OptionAttribute extends Attribute, AttributeWithOptions {
  normalize(): NormalizedOptionAttribute;
  hasOption(optionCode: OptionCode): boolean;
}

export class InvalidArgumentError extends Error {}

export class ConcreteOptionAttribute extends ConcreteAttribute implements OptionAttribute {
  private constructor(
    identifier: AttributeIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerChannel: boolean,
    valuePerLocale: boolean,
    order: number,
    is_required: boolean,
    readonly options: Option[]
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      'option',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedOptionAttribute: NormalizedOptionAttribute) {
    return new ConcreteOptionAttribute(
      denormalizeAttributeIdentifier(normalizedOptionAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedOptionAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedOptionAttribute.code),
      denormalizeLabelCollection(normalizedOptionAttribute.labels),
      normalizedOptionAttribute.value_per_channel,
      normalizedOptionAttribute.value_per_locale,
      normalizedOptionAttribute.order,
      normalizedOptionAttribute.is_required,
      normalizedOptionAttribute.options.map(createOptionFromNormalized)
    );
  }

  public hasOption(optionCode: OptionCode) {
    return this.options.some((option: Option) => optionCodesAreEqual(option.code, optionCode));
  }

  public normalize(): NormalizedOptionAttribute {
    return {
      ...super.normalize(),
      type: 'option',
      options: this.options,
    };
  }

  public setOptions(options: Option[]) {
    return new ConcreteOptionAttribute(
      this.identifier,
      this.assetFamilyIdentifier,
      this.code,
      this.labelCollection,
      this.valuePerChannel,
      this.valuePerLocale,
      this.order,
      this.isRequired,
      options
    );
  }

  public getOptions(): Option[] {
    return this.options;
  }
}

export const denormalize = ConcreteOptionAttribute.createFromNormalized;
