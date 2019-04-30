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

export interface NormalizedNumberAttribute extends NormalizedAttribute {
  type: 'number';
}

export interface NumberAttribute extends Attribute {
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
    is_required: boolean
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

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedTextAttribute: NormalizedNumberAttribute) {
    return new ConcreteNumberAttribute(
      createIdentifier(normalizedTextAttribute.identifier),
      createReferenceEntityIdentifier(normalizedTextAttribute.reference_entity_identifier),
      createCode(normalizedTextAttribute.code),
      createLabelCollection(normalizedTextAttribute.labels),
      normalizedTextAttribute.value_per_locale,
      normalizedTextAttribute.value_per_channel,
      normalizedTextAttribute.order,
      normalizedTextAttribute.is_required
    );
  }

  public normalize(): NormalizedNumberAttribute {
    return {
      ...super.normalize(),
      type: 'number',
    };
  }
}

export const denormalize = ConcreteNumberAttribute.createFromNormalized;
