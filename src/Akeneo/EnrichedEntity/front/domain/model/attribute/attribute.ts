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

interface CommonNormalizedAttribute {
  identifier: NormalizedAttributeIdentifier;
  enrichedEntityIdentifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  type: string;
  order: number;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
  required: boolean;
}

export interface NormalizedTextAttribute extends CommonNormalizedAttribute {
  maxLength: number;
}

export interface NormalizedImageAttribute extends CommonNormalizedAttribute {}

export type NormalizedAttribute = NormalizedTextAttribute | NormalizedImageAttribute;

export interface CommonAttribute {
  code: AttributeCode;
  type: AttributeType;
  order: number;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
  required: boolean;
  getIdentifier: () => Identifier;
  getCode: () => AttributeCode;
  getEnrichedEntityIdentifier: () => EnrichedEntityIdentifier;
  getLabel: (locale: string) => string;
  getLabelCollection: () => LabelCollection;
  equals: (attribute: Attribute) => boolean;
  normalize(): NormalizedAttribute;
}

export interface TextAttribute extends CommonAttribute {
  maxLength: number;
}

export interface ImageAttribute extends CommonAttribute {}

type Attribute = TextAttribute | ImageAttribute;

export default Attribute;

class InvalidArgumentError extends Error {}

class CommonConcreteAttribute implements CommonAttribute {
  protected constructor(
    readonly identifier: Identifier,
    readonly enrichedEntityIdentifier: EnrichedEntityIdentifier,
    readonly code: AttributeCode,
    readonly labelCollection: LabelCollection,
    readonly type: AttributeType,
    readonly order: number,
    readonly valuePerLocale: boolean,
    readonly valuePerChannel: boolean,
    readonly required: boolean
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Attribute expect a AttributeIdentifier as first argument');
    }
    if (!(enrichedEntityIdentifier instanceof EnrichedEntityIdentifier)) {
      throw new InvalidArgumentError('Attribute expect an EnrichedEntityIdentifier as second argument');
    }
    if (!(code instanceof AttributeCode)) {
      throw new InvalidArgumentError('Attribute expect a AttributeCode as third argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Attribute expect a LabelCollection as fourth argument');
    }
    if (!createIdentifier(enrichedEntityIdentifier.stringValue(), code.stringValue()).equals(identifier)) {
      throw new InvalidArgumentError(
        'Attribute expect an identifier complient to the given enrichedEntityIdentifier and code'
      );
    }
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

  public getLabel(locale: string) {
    return this.labelCollection.hasLabel(locale)
      ? this.labelCollection.getLabel(locale)
      : `[${this.getCode().stringValue()}]`;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(attribute: Attribute): boolean {
    return attribute.getIdentifier().equals(this.identifier);
  }

  public normalize() {
    return {
      identifier: this.identifier.normalize(),
      enrichedEntityIdentifier: this.enrichedEntityIdentifier.stringValue(),
      code: this.code.stringValue(),
      labels: this.labelCollection.normalize(),
      type: AttributeType[(this.type.charAt(0).toUpperCase() + this.type.slice(1)) as any],
      order: this.order,
      valuePerLocale: this.valuePerLocale,
      valuePerChannel: this.valuePerChannel,
      required: this.required,
    };
  }
}

class ConcreteTextAttribute extends CommonConcreteAttribute implements TextAttribute {
  private constructor(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    type: AttributeType,
    order: number,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    required: boolean,
    readonly maxLength: number
  ) {
    super(
      identifier,
      enrichedEntityIdentifier,
      code,
      labelCollection,
      type,
      order,
      valuePerLocale,
      valuePerChannel,
      required
    );

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedTextAttribute: NormalizedTextAttribute) {
    return new ConcreteTextAttribute(
      createIdentifier(
        normalizedTextAttribute.identifier.enrichedEntityIdentifier,
        normalizedTextAttribute.identifier.identifier
      ),
      createEnrichedEntityIdentifier(normalizedTextAttribute.enrichedEntityIdentifier),
      createCode(normalizedTextAttribute.code),
      createLabelCollection(normalizedTextAttribute.labels),
      AttributeType.Text,
      normalizedTextAttribute.order,
      normalizedTextAttribute.valuePerLocale,
      normalizedTextAttribute.valuePerChannel,
      normalizedTextAttribute.required,
      normalizedTextAttribute.maxLength
    );
  }

  public normalize(): NormalizedTextAttribute {
    return {
      ...super.normalize(),
      maxLength: this.maxLength,
    };
  }
}

class ConcreteImageAttribute extends CommonConcreteAttribute implements ImageAttribute {
  private constructor(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    type: AttributeType,
    order: number,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    required: boolean
  ) {
    super(
      identifier,
      enrichedEntityIdentifier,
      code,
      labelCollection,
      type,
      order,
      valuePerLocale,
      valuePerChannel,
      required
    );

    Object.freeze(this);
  }
  public static createFromNormalized(normalizedImageAttribute: NormalizedImageAttribute) {
    return new ConcreteImageAttribute(
      createIdentifier(
        normalizedImageAttribute.identifier.enrichedEntityIdentifier,
        normalizedImageAttribute.identifier.identifier
      ),
      createEnrichedEntityIdentifier(normalizedImageAttribute.enrichedEntityIdentifier),
      createCode(normalizedImageAttribute.code),
      createLabelCollection(normalizedImageAttribute.labels),
      AttributeType.Image,
      normalizedImageAttribute.order,
      normalizedImageAttribute.valuePerLocale,
      normalizedImageAttribute.valuePerChannel,
      normalizedImageAttribute.required
    );
  }
}

class InvalidAttributeTypeError extends Error {}

export const denormalizeAttribute = (normalizedAttribute: NormalizedTextAttribute | NormalizedImageAttribute) => {
  switch (normalizedAttribute.type) {
    case AttributeType.Text:
      return ConcreteTextAttribute.createFromNormalized(normalizedAttribute as NormalizedTextAttribute);
      break;
    case AttributeType.Image:
      return ConcreteImageAttribute.createFromNormalized(normalizedAttribute as NormalizedImageAttribute);
      break;
    default:
      throw new InvalidAttributeTypeError(`Attribute type "${normalizedAttribute.type}" is not supported`);
  }
};
