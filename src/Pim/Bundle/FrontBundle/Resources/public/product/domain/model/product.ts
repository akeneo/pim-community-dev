export enum ModelType {
  Product = 'product',
  ProductModel = 'product_model'
};

export interface MetaInterface {
  image: ImageInterface|null;
  id: number;
  completenesses: any;
  model_type: ModelType,
  has_children: boolean,
  label: {
    [locale: string]: string;
  }
}

export interface ImageInterface {
  filePath: string;
  originalFilename: string;
}

export interface Completeness {
  channel: string;
  locale: string;
  missing: number;
  ratio: number;
  required: number;
}

export interface RawProductInterface {
  meta: MetaInterface;
  family: string;
  identifier?: string;
  code?: string;
  children?: ProductInterface[];
}

export default interface ProductInterface extends RawProductInterface {
  getLabel(channel: string, locale: string): string;
  getCompleteness(channel: string, locale: string): Completeness;
  getImagePath(): string;
  getIdentifier(): string;
  hasChildren (): boolean;
  getChildren (): ProductInterface[];
  shouldHaveChildren (): boolean;
}

export class Product implements ProductInterface {
  readonly meta: MetaInterface;
  readonly family: string;
  readonly identifier: string;

  private constructor ({meta, family, identifier}: RawProductInterface) {
    if (undefined === identifier) {
      throw new Error('Property identifier needs to be defined to create a product');
    }

    this.meta       = meta;
    this.family     = family;
    this.identifier = identifier;
  }

  public static create(product: RawProductInterface): ProductInterface {
    return new Product(product);
  }

  public getLabel(channel: string, locale: string): string {
    return this.meta.label[locale] ? this.meta.label[locale] : this.getIdentifier();
  }

  public getCompleteness(channel: string, locale: string): Completeness {
    const completeness = this.meta.completenesses
      .find((completeness: any) => completeness.channel === channel && completeness.locale === locale)

    return undefined !== completeness ? completeness : {};
  }

  public getImagePath (): string {
    return null !== this.meta.image ? encodeURIComponent(this.meta.image.filePath) : 'undefined';
  }

  public getIdentifier (): string {
    return this.identifier;
  }

  public getChildren (): ProductInterface[] {
    return [];
  }

  public hasChildren (): boolean {
    return false;
  }

  public shouldHaveChildren (): boolean {
    return this.meta.has_children;
  }
}

export class ProductModel implements ProductInterface {
  readonly meta: MetaInterface;
  readonly family: string;
  readonly code: string;
  readonly children: ProductInterface[];

  private constructor ({meta, family, code, children}: RawProductInterface) {
    if (undefined === code) {
      throw new Error('Property code needs to be defined to create a product model');
    }

    this.meta     = meta;
    this.family   = family;
    this.code     = code;
    this.children = undefined !== children ? children : [];
  }

  public static create(product: RawProductInterface): ProductInterface {
    return new ProductModel(product);
  }

  public getLabel(channel: string, locale: string): string {
    return this.meta.label[locale] ? this.meta.label[locale] : this.getIdentifier();
  }

  public getCompleteness(channel: string, locale: string): Completeness {
    return {
      channel: '',
      locale: '',
      missing: 0,
      ratio: 0,
      required: 0
    };
  }

  public getImagePath (): string {
    return null !== this.meta.image ? encodeURIComponent(this.meta.image.filePath) : 'undefined';
  }

  public getIdentifier (): string {
    return this.code;
  }

  public getChildren (): ProductInterface[] {
    return this.children;
  }

  public hasChildren (): boolean {
    return this.children.length > 0;
  }

  public shouldHaveChildren (): boolean {
    return this.meta.has_children;
  }
}
