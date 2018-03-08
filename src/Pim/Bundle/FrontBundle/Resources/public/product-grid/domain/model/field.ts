import i18n from 'pimfront/tools/i18n';

export interface Field {
  readonly identifier: string;
  getLabel: (locale: string) => string;
}

export interface RawPropertyInterface {
  readonly identifier: string;
  readonly label: string;
}

export interface RawAttributeInterface {
  readonly identifier: string;
  labels: {[locale: string]: string};
  type: string;
}

export interface AttributeInterface extends RawAttributeInterface, Field {}
export interface PropertyInterface extends RawPropertyInterface, Field {}

export class Attribute implements AttributeInterface {
  readonly identifier: string;
  readonly labels: {[locale: string]: string};
  readonly type: string;

  private constructor({identifier, labels, type}: RawAttributeInterface) {
    if (undefined === identifier) {
      throw new Error('Property identifier needs to be defined to create an attribute');
    }

    if (undefined === type) {
      throw new Error('Property type needs to be defined to create an attribute');
    }

    this.identifier = identifier;
    this.labels = labels;
    this.type = type;
  }

  public static createFromAttribute(attribute: RawAttributeInterface) {
    return new Attribute(attribute);
  }

  public getLabel(locale: string): string {
    return i18n.getLabel(this.labels, locale, this.identifier);
  }
}

export class Property implements PropertyInterface {
  readonly identifier: string;
  readonly label: string;

  private constructor({identifier, label}: RawPropertyInterface) {
    if (undefined === identifier) {
      throw new Error('Property identifier needs to be defined to create an attribute');
    }

    this.identifier = identifier;
    this.label = label;
  }

  public static createFromProperty(property: RawPropertyInterface): PropertyInterface {
    return new Property(property);
  }

  public getLabel(locale: string): string {
    return this.label;
  }
}
