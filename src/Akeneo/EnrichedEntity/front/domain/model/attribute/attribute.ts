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

export enum ValidationRuleOptions {
  Email = 'email',
  RegularExpression = 'regular_expression',
  Url = 'url',
}

export enum AllowedExtensionsOptions {
  gif = 'gif',
  jfif = 'jfif',
  jif = 'jif',
  jpeg = 'jpeg',
  jpg = 'jpg',
  pdf = 'pdf',
  png = 'png',
  psd = 'psd',
  tif = 'tif',
  tiff = 'tiff',
}

interface CommonNormalizedAttribute {
  identifier: NormalizedAttributeIdentifier;
  enriched_entity_identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  order: number;
  value_per_locale: boolean;
  value_per_channel: boolean;
  required: boolean;
}

export interface NormalizedTextAttribute extends CommonNormalizedAttribute {
  type: 'text';
  max_length: MaxLength;
  is_textarea: IsTextarea;
  is_rich_text_editor: IsRichTextEditor;
  validation_rule: ValidationRule;
  regular_expression: RegularExpression;
}

export interface NormalizedImageAttribute extends CommonNormalizedAttribute {
  type: 'image';
  allowed_extensions: AllowedExtensions;
  max_file_size: MaxFileSize;
}

export type MaxLength = number | null;
export type MaxFileSize = number | null;
export type AllowedExtensions = AllowedExtensionsOptions[] | null;
export type IsTextarea = boolean;
export type IsRichTextEditor = boolean;
export type ValidationRule = ValidationRuleOptions | null;
export type RegularExpression = string | null;
export type AdditionalProperty =
  | MaxLength
  | MaxFileSize
  | AllowedExtensions
  | IsTextarea
  | IsRichTextEditor
  | ValidationRule
  | RegularExpression;

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
  getType(): AttributeType;
  getLabel: (locale: string) => string;
  getLabelCollection: () => LabelCollection;
  equals: (attribute: Attribute) => boolean;
  normalize(): NormalizedAttribute;
}

export interface TextAttribute extends CommonAttribute {
  maxLength: MaxLength;
  isTextarea: IsTextarea;
  isRichTextEditor: IsRichTextEditor;
  validationRule: ValidationRule;
  regularExpression: RegularExpression;
}

export interface ImageAttribute extends CommonAttribute {
  maxFileSize: MaxFileSize;
  allowedExtensions: AllowedExtensions;
}

type Attribute = TextAttribute | ImageAttribute;

export default Attribute;

class InvalidArgumentError extends Error {}

abstract class CommonConcreteAttribute implements CommonAttribute {
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

  public getType(): AttributeType {
    return this.type;
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

  protected commonNormalize(): CommonNormalizedAttribute {
    return {
      identifier: this.identifier.normalize(),
      enriched_entity_identifier: this.enrichedEntityIdentifier.stringValue(),
      code: this.code.stringValue(),
      labels: this.labelCollection.normalize(),
      order: this.order,
      value_per_locale: this.valuePerLocale,
      value_per_channel: this.valuePerChannel,
      required: this.required,
    };
  }

  public abstract normalize(): NormalizedAttribute;
}

export class ConcreteTextAttribute extends CommonConcreteAttribute implements TextAttribute {
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
    readonly maxLength: MaxLength,
    readonly isTextarea: IsTextarea,
    readonly isRichTextEditor: IsRichTextEditor,
    readonly validationRule: ValidationRule,
    readonly regularExpression: RegularExpression
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
        normalizedTextAttribute.identifier.enriched_entity_identifier,
        normalizedTextAttribute.identifier.identifier
      ),
      createEnrichedEntityIdentifier(normalizedTextAttribute.enriched_entity_identifier),
      createCode(normalizedTextAttribute.code),
      createLabelCollection(normalizedTextAttribute.labels),
      AttributeType.Text,
      normalizedTextAttribute.order,
      normalizedTextAttribute.value_per_locale,
      normalizedTextAttribute.value_per_channel,
      normalizedTextAttribute.required,
      normalizedTextAttribute.max_length,
      normalizedTextAttribute.is_textarea,
      normalizedTextAttribute.is_rich_text_editor,
      normalizedTextAttribute.validation_rule,
      normalizedTextAttribute.regular_expression
    );
  }

  public normalize(): NormalizedTextAttribute {
    return {
      ...super.commonNormalize(),
      type: 'text',
      max_length: this.maxLength,
      is_textarea: this.isTextarea,
      is_rich_text_editor: this.isRichTextEditor,
      validation_rule: this.validationRule,
      regular_expression: this.regularExpression,
    };
  }
}

export class ConcreteImageAttribute extends CommonConcreteAttribute implements ImageAttribute {
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
    readonly maxFileSize: MaxFileSize,
    readonly allowedExtensions: AllowedExtensions
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
        normalizedImageAttribute.identifier.enriched_entity_identifier,
        normalizedImageAttribute.identifier.identifier
      ),
      createEnrichedEntityIdentifier(normalizedImageAttribute.enriched_entity_identifier),
      createCode(normalizedImageAttribute.code),
      createLabelCollection(normalizedImageAttribute.labels),
      AttributeType.Image,
      normalizedImageAttribute.order,
      normalizedImageAttribute.value_per_locale,
      normalizedImageAttribute.value_per_channel,
      normalizedImageAttribute.required,
      normalizedImageAttribute.max_file_size,
      normalizedImageAttribute.allowed_extensions
    );
  }

  public normalize(): NormalizedImageAttribute {
    return {
      ...super.commonNormalize(),
      type: 'image',
      max_file_size: this.maxFileSize,
      allowed_extensions: this.allowedExtensions,
    };
  }
}

class InvalidAttributeTypeError extends Error {}

export const denormalizeAttribute = (normalizedAttribute: NormalizedTextAttribute | NormalizedImageAttribute) => {
  switch (normalizedAttribute.type) {
    case AttributeType.Text:
      return ConcreteTextAttribute.createFromNormalized(normalizedAttribute as NormalizedTextAttribute);
    case AttributeType.Image:
      return ConcreteImageAttribute.createFromNormalized(normalizedAttribute as NormalizedImageAttribute);
    default:
      throw new InvalidAttributeTypeError(`Attribute type "${normalizedAttribute.type}" is not supported`);
  }
};
