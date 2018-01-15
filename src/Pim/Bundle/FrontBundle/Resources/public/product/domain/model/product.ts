export interface MetaInterface {
  image: string;
  id: number;
  label: {
    [locale: string]: string;
  }
}

export interface RawProductInterface {
  meta: MetaInterface;
  family: string;
  identifier: string;
}

export interface ProductInterface extends RawProductInterface {
  getLabel(channel: string, locale: string): string;
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
}
