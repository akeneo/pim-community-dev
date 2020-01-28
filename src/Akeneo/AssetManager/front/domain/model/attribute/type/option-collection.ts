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
import {AttributeWithOptions} from 'akeneoassetmanager/domain/model/attribute/type/option';

export const OPTION_COLLECTION_ATTRIBUTE_TYPE = 'option_collection';

export interface NormalizedOptionCollectionAttribute extends NormalizedAttribute {
  type: typeof OPTION_COLLECTION_ATTRIBUTE_TYPE;
  options: Option[];
}

export const isOptionCollectionAttribute = (
  optionCollectionAttribute: NormalizedAttribute
): optionCollectionAttribute is NormalizedOptionCollectionAttribute =>
  optionCollectionAttribute.type === OPTION_COLLECTION_ATTRIBUTE_TYPE;

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
    is_read_only: boolean,
    readonly options: Option[]
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      OPTION_COLLECTION_ATTRIBUTE_TYPE,
      valuePerLocale,
      valuePerChannel,
      order,
      is_required,
      is_read_only
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
      normalizedOptionCollectionAttribute.is_read_only,
      normalizedOptionCollectionAttribute.options.map(createOptionFromNormalized)
    );
  }

  public normalize(): NormalizedOptionCollectionAttribute {
    return {
      ...super.normalize(),
      type: OPTION_COLLECTION_ATTRIBUTE_TYPE,
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
      this.isReadOnly,
      options
    );
  }

  public getOptions(): Option[] {
    return this.options;
  }
}

export const denormalize = ConcreteOptionCollectionAttribute.createFromNormalized;
