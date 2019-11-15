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
import {AttributeWithOptions} from './option';

export interface NormalizedOptionCollectionAttribute extends NormalizedAttribute {
  type: 'option_collection';
  options: Option[];
}

export type NormalizedOptionCollectionAdditionalProperty = Option;
export type OptionCollectionAdditionalProperty = Option;

export interface OptionCollectionAttribute extends Attribute, AttributeWithOptions {
  normalize(): NormalizedOptionCollectionAttribute;
}

export class InvalidArgumentError extends Error {}

// Todo: Invert valuePerLocale and valuePerChannel order in the constructor
export class ConcreteOptionCollectionAttribute extends ConcreteAttribute implements OptionCollectionAttribute {
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
      'option_collection',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedOptionCollectionAttribute: NormalizedOptionCollectionAttribute) {
    return new ConcreteOptionCollectionAttribute(
      denormalizeAttributeIdentifier(normalizedOptionCollectionAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedOptionCollectionAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedOptionCollectionAttribute.code),
      denormalizeLabelCollection(normalizedOptionCollectionAttribute.labels),
      normalizedOptionCollectionAttribute.value_per_channel,
      normalizedOptionCollectionAttribute.value_per_locale,
      normalizedOptionCollectionAttribute.order,
      normalizedOptionCollectionAttribute.is_required,
      normalizedOptionCollectionAttribute.options.map(createOptionFromNormalized)
    );
  }

  public normalize(): NormalizedOptionCollectionAttribute {
    return {
      ...super.normalize(),
      type: 'option_collection',
      options: this.options,
    };
  }

  public setOptions(options: Option[]) {
    return new ConcreteOptionCollectionAttribute(
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

export const denormalize = ConcreteOptionCollectionAttribute.createFromNormalized;
