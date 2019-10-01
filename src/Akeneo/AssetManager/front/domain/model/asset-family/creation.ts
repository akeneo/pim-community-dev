import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {
  denormalizeAttributeCode,
  attributecodesAreEqual,
  attributeCodeStringValue,
} from 'akeneoassetmanager/domain/model/attribute/code';

export interface NormalizedAssetFamilyCreation {
  code: AttributeCode;
  labels: NormalizedLabelCollection;
}

export default interface AssetFamilyCreation {
  getCode: () => AttributeCode;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  equals: (assetFamilyCreation: AssetFamilyCreation) => boolean;
  normalize: () => NormalizedAssetFamilyCreation;
}

class AssetFamilyCreationImplementation implements AssetFamilyCreation {
  private constructor(private code: AttributeCode, private labelCollection: LabelCollection) {
    Object.freeze(this);
  }

  public static create(code: AttributeCode, labelCollection: LabelCollection): AssetFamilyCreation {
    return new AssetFamilyCreationImplementation(code, labelCollection);
  }

  public static createEmpty(): AssetFamilyCreation {
    return new AssetFamilyCreationImplementation(denormalizeAttributeCode(''), createLabelCollection({}));
  }

  public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamilyCreation): AssetFamilyCreation {
    const code = denormalizeAttributeCode(normalizedAssetFamily.code);
    const labelCollection = createLabelCollection(normalizedAssetFamily.labels);

    return AssetFamilyCreationImplementation.create(code, labelCollection);
  }

  public getCode(): AttributeCode {
    return this.code;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getCode()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(assetFamilyCreation: AssetFamilyCreation): boolean {
    return attributecodesAreEqual(assetFamilyCreation.getCode(), this.code);
  }

  public normalize(): NormalizedAssetFamilyCreation {
    return {
      code: attributeCodeStringValue(this.getCode()),
      labels: this.getLabelCollection().normalize(),
    };
  }
}

export const createEmptyAssetFamilyCreation = AssetFamilyCreationImplementation.createEmpty;
export const denormalizeAssetFamilyCreation = AssetFamilyCreationImplementation.createFromNormalized;
