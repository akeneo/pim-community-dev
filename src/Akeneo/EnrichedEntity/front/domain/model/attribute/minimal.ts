import Identifier, {
  NormalizedAttributeIdentifier,
  createIdentifier,
} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoenrichedentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';

export enum AttributeType {
  Text = 'text',
  Image = 'image',
}

export interface MinimalNormalizedAttribute {
  identifier: NormalizedAttributeIdentifier;
  enriched_entity_identifier: string;
  type: 'text' | 'image';
  code: string;
  labels: NormalizedLabelCollection;
  value_per_locale: boolean;
  value_per_channel: boolean;
}

export default interface MinimalAttribute {
  identifier: Identifier;
  enrichedEntityIdentifier: EnrichedEntityIdentifier;
  code: AttributeCode;
  labelCollection: LabelCollection;
  type: AttributeType;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
  getCode: () => AttributeCode;
  getIdentifier: () => Identifier;
  getEnrichedEntityIdentifier: () => EnrichedEntityIdentifier;
  getType(): AttributeType;
  getLabel: (locale: string, defaultValue?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  equals: (attribute: MinimalAttribute) => boolean;
  normalize(): MinimalNormalizedAttribute;
}

class InvalidArgumentError extends Error {}

export class MinimalConcreteAttribute implements MinimalAttribute {
  protected constructor(
    readonly identifier: Identifier,
    readonly enrichedEntityIdentifier: EnrichedEntityIdentifier,
    readonly code: AttributeCode,
    readonly labelCollection: LabelCollection,
    readonly type: AttributeType,
    readonly valuePerLocale: boolean,
    readonly valuePerChannel: boolean
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Attribute expect an AttributeIdentifier argument');
    }
    if (!(enrichedEntityIdentifier instanceof EnrichedEntityIdentifier)) {
      throw new InvalidArgumentError('Attribute expect an EnrichedEntityIdentifier argument');
    }
    if (!(code instanceof AttributeCode)) {
      throw new InvalidArgumentError('Attribute expect a AttributeCode argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Attribute expect a LabelCollection argument');
    }
    if (!createIdentifier(enrichedEntityIdentifier.stringValue(), code.stringValue()).equals(identifier)) {
      throw new InvalidArgumentError(
        'Attribute expect an identifier complient to the given enrichedEntityIdentifier and code'
      );
    }
    if (typeof type !== 'string' && !Object.values(AttributeType).includes(type)) {
      throw new InvalidArgumentError(
        `Attribute expect valid attribute type (${Object.values(AttributeType).join(', ')})`
      );
    }
    if (typeof valuePerLocale !== 'boolean') {
      throw new InvalidArgumentError('Attribute expect a boolean as valuePerLocale');
    }
    if (typeof valuePerChannel !== 'boolean') {
      throw new InvalidArgumentError('Attribute expect a boolean as valuePerChannel');
    }
  }

  public static createFromNormalized(minimalNormalizedAttribute: MinimalNormalizedAttribute) {
    return new MinimalConcreteAttribute(
      createIdentifier(
        minimalNormalizedAttribute.identifier.enriched_entity_identifier,
        minimalNormalizedAttribute.identifier.identifier
      ),
      createEnrichedEntityIdentifier(minimalNormalizedAttribute.enriched_entity_identifier),
      createCode(minimalNormalizedAttribute.code),
      createLabelCollection(minimalNormalizedAttribute.labels),
      minimalNormalizedAttribute.type as AttributeType,
      minimalNormalizedAttribute.value_per_locale,
      minimalNormalizedAttribute.value_per_channel
    );
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getEnrichedEntityIdentifier(): EnrichedEntityIdentifier {
    return this.enrichedEntityIdentifier;
  }

  public getCode(): AttributeCode {
    return this.code;
  }

  public getType(): AttributeType {
    return this.type;
  }

  public getLabel(locale: string, defaultValue: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return defaultValue ? `[${this.getCode().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(attribute: MinimalAttribute): boolean {
    return attribute.getIdentifier().equals(this.identifier);
  }

  public normalize(): MinimalNormalizedAttribute {
    return {
      identifier: this.identifier.normalize(),
      enriched_entity_identifier: this.enrichedEntityIdentifier.stringValue(),
      code: this.code.stringValue(),
      type: this.getType(),
      labels: this.labelCollection.normalize(),
      value_per_locale: this.valuePerLocale,
      value_per_channel: this.valuePerChannel,
    };
  }
}

export const denormalizeMinimalAttribute = (normalizedAttribute: MinimalNormalizedAttribute) => {
  return MinimalConcreteAttribute.createFromNormalized(normalizedAttribute);
};
