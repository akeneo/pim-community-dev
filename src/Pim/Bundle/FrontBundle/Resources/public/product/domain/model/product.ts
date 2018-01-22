export interface MetaInterface {
  image: ImageInterface|null;
  id: number;
  completenesses: any;
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
  identifier: string;
}

export interface ProductInterface extends RawProductInterface {
  getLabel(channel: string, locale: string): string;
  getCompleteness(channel: string, locale: string): Completeness;
  getImagePath (): string;
}

export default class Product implements ProductInterface {
  readonly meta: MetaInterface;
  readonly family: string;
  readonly identifier: string;

  private constructor ({meta, family, identifier}: RawProductInterface) {
    this.meta = meta;
    this.family = family;
    this.identifier = identifier;
  }

  public static clone(product: RawProductInterface) {
    return new Product(product)
  }

  public getLabel(channel: string, locale: string): string {
    return this.meta.label[locale] ? this.meta.label[locale] : this.identifier;
  }

  public getCompleteness(channel: string, locale: string): Completeness {
    const completeness = this.meta.completenesses
      .find((completeness: any) => completeness.channel === channel && completeness.locale === locale)

    return undefined !== completeness ? completeness : {};
  }

  public getImagePath (): string {
    return null !== this.meta.image ? encodeURIComponent(this.meta.image.filePath) : 'undefined';
  }
}
