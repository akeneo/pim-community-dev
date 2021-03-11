import ProductIdentifier, {
  denormalizeProductIdentifier,
  productidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/product/identifier';
import LabelCollection, {
  denormalizeLabelCollection,
  getLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import {File, createFileFromNormalized} from 'akeneoassetmanager/domain/model/file';
import Completeness, {
  NormalizedCompleteness,
  denormalizeCompleteness,
} from 'akeneoassetmanager/domain/model/product/completeness';

export const PRODUCT_TYPE = 'product';
export const PRODUCT_MODEL_TYPE = 'product_model';
export type ProductType = 'product' | 'product_model';

export const isProductModel = ({type}: NormalizedProduct): boolean => PRODUCT_MODEL_TYPE === type;

type NormalizedProductIdentifier = string;
type NormalizedProductId = string;

export interface NormalizedProduct {
  id: NormalizedProductId;
  identifier: NormalizedProductIdentifier;
  type: ProductType;
  labels: LabelCollection;
  image: File;
  completeness: NormalizedCompleteness;
}

export default interface Product {
  getId: () => ProductIdentifier;
  getIdentifier: () => ProductIdentifier;
  getType: () => ProductType;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  getCompleteness: () => Completeness;
  equals: (product: Product) => boolean;
  normalize: () => NormalizedProduct;
}
class InvalidArgumentError extends Error {}

class ProductImplementation implements Product {
  private constructor(
    private id: ProductIdentifier,
    private identifier: ProductIdentifier,
    private type: ProductType,
    private labelCollection: LabelCollection,
    private image: File,
    private completeness: Completeness
  ) {
    if (!['product', 'product_model'].includes(type)) {
      throw new InvalidArgumentError('Product expects an ProductType as type argument');
    }
    if (!(completeness instanceof Completeness)) {
      throw new InvalidArgumentError('Product expects a Completeness as completeness argument');
    }

    Object.freeze(this);
  }

  public static create(
    id: ProductIdentifier,
    identifier: ProductIdentifier,
    type: ProductType,
    labelCollection: LabelCollection,
    image: File,
    completeness: Completeness
  ): Product {
    return new ProductImplementation(id, identifier, type, labelCollection, image, completeness);
  }

  public static createFromNormalized(normalizedProduct: NormalizedProduct): Product {
    const id = denormalizeProductIdentifier(normalizedProduct.id);
    const type = normalizedProduct.type;
    const identifier = denormalizeProductIdentifier(normalizedProduct.identifier);
    const labelCollection = denormalizeLabelCollection(normalizedProduct.labels);
    const image = createFileFromNormalized(normalizedProduct.image);
    const completeness = denormalizeCompleteness(normalizedProduct.completeness);

    return ProductImplementation.create(id, identifier, type, labelCollection, image, completeness);
  }

  public getId(): ProductIdentifier {
    return this.id;
  }

  public getIdentifier(): ProductIdentifier {
    return this.identifier;
  }

  public getType(): ProductType {
    return this.type;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    return getLabelInCollection(this.labelCollection, locale, fallbackOnCode, this.getIdentifier());
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public getImage(): File {
    return this.image;
  }

  public getCompleteness(): Completeness {
    return this.completeness;
  }

  public equals(product: Product): boolean {
    return productidentifiersAreEqual(product.getIdentifier(), this.identifier);
  }

  public normalize(): NormalizedProduct {
    return {
      id: this.getId(),
      identifier: this.getIdentifier(),
      type: this.getType(),
      labels: this.getLabelCollection(),
      image: this.getImage(),
      completeness: this.getCompleteness().normalize(),
    };
  }
}

export const createProduct = ProductImplementation.create;
export const denormalizeProduct = ProductImplementation.createFromNormalized;
