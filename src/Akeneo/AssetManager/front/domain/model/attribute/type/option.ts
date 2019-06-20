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
import OptionCode from 'akeneoreferenceentity/domain/model/attribute/type/option/option-code';

export interface NormalizedOptionAttribute extends NormalizedAttribute {
  type: 'option';
  options: NormalizedOption[];
}

export type NormalizedOptionAdditionalProperty = NormalizedOption;
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
      'option',
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

  public static createFromNormalized(normalizedOptionAttribute: NormalizedOptionAttribute) {
    return new ConcreteOptionAttribute(
      createIdentifier(normalizedOptionAttribute.identifier),
      createReferenceEntityIdentifier(normalizedOptionAttribute.reference_entity_identifier),
      createCode(normalizedOptionAttribute.code),
      createLabelCollection(normalizedOptionAttribute.labels),
      normalizedOptionAttribute.value_per_channel,
      normalizedOptionAttribute.value_per_locale,
      normalizedOptionAttribute.order,
      normalizedOptionAttribute.is_required,
      normalizedOptionAttribute.options.map(Option.createFromNormalized)
    );
  }

  public hasOption(optionCode: OptionCode) {
    return this.options.some((option: Option) => option.code.equals(optionCode));
  }

  public normalize(): NormalizedOptionAttribute {
    return {
      ...super.normalize(),
      type: 'option',
      options: this.options.map((option: Option) => option.normalize()),
    };
  }

  public setOptions(options: Option[]) {
    return new ConcreteOptionAttribute(
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

export const denormalize = ConcreteOptionAttribute.createFromNormalized;
