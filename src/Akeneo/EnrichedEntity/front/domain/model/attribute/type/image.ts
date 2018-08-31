import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import {
  CommonNormalizedAttribute,
  CommonAttribute,
  CommonConcreteAttribute,
} from 'akeneoenrichedentity/domain/model/attribute/common';
import {NormalizedMaxFileSize, MaxFileSize} from 'akeneoenrichedentity/domain/model/attribute/type/image/max-file-size';
import {NormalizedAllowedExtensions, AllowedExtensions} from './image/allowed-extensions';

export interface NormalizedImageAttribute extends CommonNormalizedAttribute {
  type: 'image';
  allowed_extensions: NormalizedAllowedExtensions;
  max_file_size: NormalizedMaxFileSize;
}

export type NormalizedImageAdditionalProperty = NormalizedMaxFileSize | NormalizedAllowedExtensions;

export type ImageAdditionalProperty = MaxFileSize | AllowedExtensions;

export interface ImageAttribute extends CommonAttribute {
  maxFileSize: MaxFileSize;
  allowedExtensions: AllowedExtensions;
  normalize(): NormalizedImageAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteImageAttribute extends CommonConcreteAttribute implements ImageAttribute {
  private constructor(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
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
      enrichedEntityIdentifier,
      code,
      labelCollection,
      AttributeType.Image,
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    if (!(maxFileSize instanceof MaxFileSize)) {
      throw new InvalidArgumentError('Attribute expect a MaxFileSize as maxFileSize');
    }

    if (!(allowedExtensions instanceof AllowedExtensions)) {
      throw new InvalidArgumentError('Attribute expect a AllowedExtension as allowedExtension');
    }

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedImageAttribute: NormalizedImageAttribute) {
    return new ConcreteImageAttribute(
      createIdentifier(normalizedImageAttribute.identifier),
      createEnrichedEntityIdentifier(normalizedImageAttribute.enriched_entity_identifier),
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
