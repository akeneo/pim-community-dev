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
import {
  NormalizedMaxFileSize,
  MaxFileSize,
} from 'akeneoreferenceentity/domain/model/attribute/type/image/max-file-size';
import {NormalizedAllowedExtensions, AllowedExtensions} from './image/allowed-extensions';

export interface NormalizedImageAttribute extends NormalizedAttribute {
  type: 'image';
  allowed_extensions: NormalizedAllowedExtensions;
  max_file_size: NormalizedMaxFileSize;
}

export type NormalizedImageAdditionalProperty = NormalizedMaxFileSize | NormalizedAllowedExtensions;

export type ImageAdditionalProperty = MaxFileSize | AllowedExtensions;

export interface ImageAttribute extends Attribute {
  maxFileSize: MaxFileSize;
  allowedExtensions: AllowedExtensions;
  normalize(): NormalizedImageAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteImageAttribute extends ConcreteAttribute implements ImageAttribute {
  private constructor(
    identifier: Identifier,
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly maxFileSize: MaxFileSize,
    readonly allowedExtensions: AllowedExtensions
  ) {
    super(
      identifier,
      referenceEntityIdentifier,
      code,
      labelCollection,
      'image',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    if (!(maxFileSize instanceof MaxFileSize)) {
      throw new InvalidArgumentError('Attribute expects a MaxFileSize as maxFileSize');
    }

    if (!(allowedExtensions instanceof AllowedExtensions)) {
      throw new InvalidArgumentError('Attribute expects a AllowedExtension as allowedExtension');
    }

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedImageAttribute: NormalizedImageAttribute) {
    return new ConcreteImageAttribute(
      createIdentifier(normalizedImageAttribute.identifier),
      createReferenceEntityIdentifier(normalizedImageAttribute.reference_entity_identifier),
      createCode(normalizedImageAttribute.code),
      createLabelCollection(normalizedImageAttribute.labels),
      normalizedImageAttribute.value_per_locale,
      normalizedImageAttribute.value_per_channel,
      normalizedImageAttribute.order,
      normalizedImageAttribute.is_required,
      MaxFileSize.createFromNormalized(normalizedImageAttribute.max_file_size),
      AllowedExtensions.createFromNormalized(normalizedImageAttribute.allowed_extensions)
    );
  }

  public normalize(): NormalizedImageAttribute {
    return {
      ...super.normalize(),
      type: 'image',
      max_file_size: this.maxFileSize.normalize(),
      allowed_extensions: this.allowedExtensions.normalize(),
    };
  }
}

export const denormalize = ConcreteImageAttribute.createFromNormalized;
