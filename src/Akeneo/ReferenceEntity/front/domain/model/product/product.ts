import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/product/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoreferenceentity/domain/model/label-collection';
import File, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import Completeness, {
  NormalizedCompleteness,
  denormalizeCompleteness,
} from 'akeneoreferenceentity/domain/model/product/completeness';

export const PRODUCT_TYPE = 'product';
export const PRODUCT_MODEL_TYPE = 'product_model';
export type ProductType = 'product' | 'product_model';

export const isProductModel = (product: Product): boolean => PRODUCT_MODEL_TYPE === product.getType();

type NormalizedProductIdentifier = string;
type NormalizedProductId = string;

export interface NormalizedProduct {
  id: NormalizedProductId;
  identifier: NormalizedProductIdentifier;
  type: ProductType;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
  completeness: NormalizedCompleteness;
}

export default interface Product {
  getId: () => Identifier;
  getIdentifier: () => Identifier;
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
    private id: Identifier,
    private identifier: Identifier,
    private type: ProductType,
    private labelCollection: LabelCollection,
    private image: File,
    private completeness: Completeness
  ) {
    if (!(id instanceof Identifier)) {
      throw new InvalidArgumentError('Product expects an ProductIdentifier as id argument');
    }
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Product expects an ProductIdentifier as identifier argument');
    }
    if (!['product', 'product_model'].includes(type)) {
      throw new InvalidArgumentError('Product expects an ProductType as type argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Product expects a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('Product expects a File as image argument');
    }
    if (!(completeness instanceof Completeness)) {
      throw new InvalidArgumentError('Product expects a Completeness as completeness argument');
    }

    Object.freeze(this);
  }

  public static create(
    id: Identifier,
    identifier: Identifier,
    type: ProductType,
    labelCollection: LabelCollection,
    image: File,
    completeness: Completeness
  ): Product {
    return new ProductImplementation(id, identifier, type, labelCollection, image, completeness);
  }

  public static createFromNormalized(normalizedProduct: NormalizedProduct): Product {
    const id = createIdentifier(normalizedProduct.id);
    const type = normalizedProduct.type;
    const identifier = createIdentifier(normalizedProduct.identifier);
    const labelCollection = createLabelCollection(normalizedProduct.labels);
    const image = denormalizeFile(normalizedProduct.image);
    const completeness = denormalizeCompleteness(normalizedProduct.completeness);

    return ProductImplementation.create(id, identifier, type, labelCollection, image, completeness);
  }

  public getId(): Identifier {
    return this.id;
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getType(): ProductType {
    return this.type;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getIdentifier().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
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
    return product.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedProduct {
    return {
      id: this.getId().stringValue(),
      identifier: this.getIdentifier().stringValue(),
      type: this.getType(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      completeness: this.getCompleteness().normalize(),
    };
  }
}

export const createProduct = ProductImplementation.create;
export const denormalizeProduct = ProductImplementation.createFromNormalized;
