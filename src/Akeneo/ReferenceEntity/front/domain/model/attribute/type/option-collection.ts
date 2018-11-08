import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ReferenceEntityIdentifier, {createIdentifier as createReferenceEntityIdentifier,} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {
  Attribute,
  ConcreteAttribute,
  NormalizedAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';

export interface NormalizedOptionCollectionAttribute extends NormalizedAttribute {
  type: 'option_collection';
  options: NormalizedOption[];
}

export type NormalizedOptionCollectionAdditionalProperty = NormalizedOption;
export type OptionCollectionAdditionalProperty = Option;

export interface OptionCollectionAttribute extends Attribute {
  options: Option[];
  normalize(): NormalizedOptionCollectionAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteOptionCollectionAttribute extends ConcreteAttribute implements OptionCollectionAttribute {
  private constructor(
    identifier: Identifier,
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly options: Option[]
  ) {
    super(
      identifier,
      referenceEntityIdentifier,
      code,
      labelCollection,
      'option_collection',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    options.map((option) => {
      if (!(option instanceof Option)) {
        throw new InvalidArgumentError('Attribute expects a list of Option as options');
      }
    });

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedOptionCollectionAttribute: NormalizedOptionCollectionAttribute) {
    return new ConcreteOptionCollectionAttribute(
      createIdentifier(normalizedOptionCollectionAttribute.identifier),
      createReferenceEntityIdentifier(normalizedOptionCollectionAttribute.reference_entity_identifier),
      createCode(normalizedOptionCollectionAttribute.code),
      createLabelCollection(normalizedOptionCollectionAttribute.labels),
      normalizedOptionCollectionAttribute.value_per_locale,
      normalizedOptionCollectionAttribute.value_per_channel,
      normalizedOptionCollectionAttribute.order,
      normalizedOptionCollectionAttribute.is_required,
      normalizedOptionCollectionAttribute.options.map(Option.createFromNormalized)
    );
  }

  public normalize(): NormalizedOptionCollectionAttribute {
    return {
      ...super.normalize(),
      type: 'option_collection',
      options: this.options.map((option:Option) => option.normalize())
    };
  }
}

export const denormalize = ConcreteOptionCollectionAttribute.createFromNormalized;
