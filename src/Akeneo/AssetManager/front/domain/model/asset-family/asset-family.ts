import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {
  getLabelInCollection,
  denormalizeLabelCollection,
  emptyLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import {File, createFileFromNormalized, createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import AssetFamilyCode from 'akeneoassetmanager/domain/model/asset-family/code';

export interface AssetFamily {
  identifier: AssetFamilyIdentifier;
  code: AssetFamilyCode;
  labels: LabelCollection;
  image: File;
  attribute_as_label: AttributeIdentifier;
  attribute_as_image: AttributeIdentifier;
}

// class AssetFamilyImplementation implements AssetFamily {
//   private constructor(
//     private identifier: AssetFamilyIdentifier,
//     private labelCollection: LabelCollection,
//     private image: File,
//     private attributeAsLabel: AttributeIdentifier,
//     private attributeAsImage: AttributeIdentifier
//   ) {
//     Object.freeze(this);
//   }

//   public static create(
//     identifier: AssetFamilyIdentifier,
//     labelCollection: LabelCollection,
//     image: File,
//     attributeAsLabel: AttributeIdentifier,
//     attributeAsImage: AttributeIdentifier
//   ): AssetFamily {
//     return new AssetFamilyImplementation(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
//   }

//   public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamily): AssetFamily {
//     const identifier = denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier);
//     const labelCollection = denormalizeLabelCollection(normalizedAssetFamily.labels);
//     const image = createFileFromNormalized(normalizedAssetFamily.image);
//     const attributeAsLabel = denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_label);
//     const attributeAsImage = denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_image);

//     return AssetFamilyImplementation.create(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
//   }

//   public getIdentifier(): AssetFamilyIdentifier {
//     return this.identifier;
//   }

//   public getLabel(locale: string, fallbackOnCode: boolean = true) {
//     return getLabelInCollection(this.labelCollection, locale, fallbackOnCode, this.getIdentifier());
//   }

//   public getLabelCollection(): LabelCollection {
//     return this.labelCollection;
//   }

//   public getImage(): File {
//     return this.image;
//   }

//   public getAttributeAsLabel(): AttributeIdentifier {
//     return this.attributeAsLabel;
//   }

//   public getAttributeAsImage(): AttributeIdentifier {
//     return this.attributeAsImage;
//   }

//   public equals(assetFamily: AssetFamily): boolean {
//     return assetFamilyidentifiersAreEqual(assetFamily.getIdentifier(), this.identifier);
//   }

//   public normalize(): NormalizedAssetFamily {
//     return {
//       identifier: this.getIdentifier(),
//       code: this.getIdentifier(),
//       labels: this.getLabelCollection(),
//       image: this.getImage(),
//       attribute_as_label: this.getAttributeAsLabel().normalize(),
//       attribute_as_image: this.getAttributeAsImage().normalize(),
//     };
//   }
// }

export const createEmptyAssetFamily = () => ({
  identifier: '',
  code: '',
  labels: emptyLabelCollection(),
  image: createEmptyFile(),
  attribute_as_image: '',
  attribute_as_label: '',
});
export const createAssetFamilyFromNormalized = (normalizedAssetFamily: any) => ({
  identifier: denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier),
  code: denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier),
  labels: denormalizeLabelCollection(normalizedAssetFamily.labels),
  image: createFileFromNormalized(normalizedAssetFamily.image),
  attribute_as_image: denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_label),
  attribute_as_label: denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_image),
});
export const getAssetFamilyLabel = (assetFamily: AssetFamily, locale: LocaleCode, fallbackOnCode: boolean = true) => {
  return getLabelInCollection(assetFamily.labels, locale, fallbackOnCode, assetFamily.code);
};
export const assetFamilyAreEqual = (first: AssetFamily, second: AssetFamily) =>
  assetFamilyidentifiersAreEqual(first.identifier, second.identifier);
