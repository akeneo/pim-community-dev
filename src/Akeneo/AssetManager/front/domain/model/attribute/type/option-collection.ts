import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ReferenceEntityIdentifier, {
  createIdentifier as createReferenceEntityIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {
  Attribute,
  ConcreteAttribute,
  NormalizedAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import {AttributeWithOptions} from './option';

export interface NormalizedOptionCollectionAttribute extends NormalizedAttribute {
  type: 'option_collection';
  options: NormalizedOption[];
}

export type NormalizedOptionCollectionAdditionalProperty = NormalizedOption;
export type OptionCollectionAdditionalProperty = Option;

export interface OptionCollectionAttribute extends Attribute, AttributeWithOptions {
  normalize(): NormalizedOptionCollectionAttribute;
}

export class InvalidArgumentError extends Error {}

// Todo: Invert valuePerLocale and valuePerChannel order in the constructor
export class ConcreteOptionCollectionAttribute extends ConcreteAttribute implements OptionCollectionAttribute {
  private constructor(
    identifier: Identifier,
    referenceEntityIdentifier: ReferenceEntityIdentifier,
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
      referenceEntityIdentifier,
      code,
      labelCollection,
      'option_collection',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    options.map(option => {
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
      normalizedOptionCollectionAttribute.value_per_channel,
      normalizedOptionCollectionAttribute.value_per_locale,
      normalizedOptionCollectionAttribute.order,
      normalizedOptionCollectionAttribute.is_required,
      normalizedOptionCollectionAttribute.options.map(Option.createFromNormalized)
    );
  }

  public normalize(): NormalizedOptionCollectionAttribute {
    return {
      ...super.normalize(),
      type: 'option_collection',
      options: this.options.map((option: Option) => option.normalize()),
    };
  }

  public setOptions(options: Option[]) {
    return new ConcreteOptionCollectionAttribute(
      this.identifier,
      this.referenceEntityIdentifier,
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
