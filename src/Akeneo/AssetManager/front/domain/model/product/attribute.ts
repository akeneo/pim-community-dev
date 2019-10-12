import LabelCollection, {
  denormalizeLabelCollection,
  getLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode from 'akeneoassetmanager/domain/model/product/attribute/code';
import {denormalizeAttributeCode, attributecodesAreEqual} from 'akeneoassetmanager/domain/model/attribute/code';

type AttributeType = string;
type UseableAsGridFilter = boolean;

type NormalizedAttributeType = string;
type NormalizedReferenceDataName = string;
type ReferenceDataName = string;
type NormalizedUseableAsGridFilter = boolean;

export interface NormalizedAttribute {
  code: AttributeCode;
  type: NormalizedAttributeType;
  labels: LabelCollection;
  reference_data_name: NormalizedReferenceDataName;
  useable_as_grid_filter: NormalizedUseableAsGridFilter;
}

export default interface Attribute {
  getCode: () => AttributeCode;
  getType: () => AttributeType;
  getReferenceDataName: () => ReferenceDataName;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getUseableAsGridFilter: () => UseableAsGridFilter;
  equals: (attribute: Attribute) => boolean;
  normalize: () => NormalizedAttribute;
}
class InvalidArgumentError extends Error {}

class AttributeImplementation implements Attribute {
  private constructor(
    private code: AttributeCode,
    private type: AttributeType,
    private labelCollection: LabelCollection,
    private referenceDataName: ReferenceDataName,
    private useableAsGridFilter: UseableAsGridFilter
  ) {
    if (typeof type !== 'string') {
      throw new InvalidArgumentError('Attribute expects a string as type argument');
    }
    if (typeof referenceDataName !== 'string') {
      throw new InvalidArgumentError('Attribute expects a string as referenceDataName argument');
    }
    if (typeof useableAsGridFilter !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as type argument');
    }

    Object.freeze(this);
  }

  public static create(
    code: AttributeCode,
    type: AttributeType,
    labelCollection: LabelCollection,
    referenceDataName: ReferenceDataName,
    useableAsGridFilter: UseableAsGridFilter
  ): Attribute {
    return new AttributeImplementation(code, type, labelCollection, referenceDataName, useableAsGridFilter);
  }

  public static createFromNormalized(normalizedAttribute: NormalizedAttribute): Attribute {
    const code = denormalizeAttributeCode(normalizedAttribute.code);
    const type = normalizedAttribute.type;
    const referenceDataName = normalizedAttribute.reference_data_name;
    const labelCollection = denormalizeLabelCollection(normalizedAttribute.labels);
    const useableAsGridFilter = normalizedAttribute.useable_as_grid_filter;

    return AttributeImplementation.create(code, type, labelCollection, referenceDataName, useableAsGridFilter);
  }

  public getCode(): AttributeCode {
    return this.code;
  }

  public getType(): AttributeType {
    return this.type;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    return getLabelInCollection(this.labelCollection, locale, fallbackOnCode, this.getCode());
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public getReferenceDataName(): ReferenceDataName {
    return this.referenceDataName;
  }

  public getUseableAsGridFilter(): UseableAsGridFilter {
    return this.useableAsGridFilter;
  }

  public equals(attribute: Attribute): boolean {
    return attributecodesAreEqual(attribute.getCode(), this.code);
  }

  public normalize(): NormalizedAttribute {
    return {
      code: this.getCode(),
      type: this.getType(),
      labels: this.getLabelCollection(),
      reference_data_name: this.getReferenceDataName(),
      useable_as_grid_filter: this.getUseableAsGridFilter(),
    };
  }
}

export const createAttribute = AttributeImplementation.create;
export const denormalizeAttribute = AttributeImplementation.createFromNormalized;
