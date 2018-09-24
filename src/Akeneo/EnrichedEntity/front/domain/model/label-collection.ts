export interface NormalizedLabelCollection {
  [locale: string]: string;
}

class InvalidTypeError extends Error {}
class UnknownLocaleError extends Error {}

const ensureString = (value: string) => {
  if ('string' !== typeof value) {
    throw new InvalidTypeError('LabelCollection expect only values as {"en_US": "My label"} to be created');
  }
};

export default class LabelCollection {
  private constructor(private labels: NormalizedLabelCollection) {
    if ('object' !== typeof labels) {
      throw new InvalidTypeError('LabelCollection expect only values as {"en_US": "My label"} to be created');
    }

    Object.keys(labels).forEach((key: string) => {
      ensureString(labels[key]);
    });

    Object.freeze(this);
  }

  public static create(labels: NormalizedLabelCollection): LabelCollection {
    return new LabelCollection(labels);
  }

  public hasLabel(locale: string): boolean {
    return 'string' === typeof this.labels[locale] && this.labels[locale].length > 0;
  }

  public getLabel(locale: string): string {
    if (!this.hasLabel(locale)) {
      throw new UnknownLocaleError(`The label for locale ${locale} doesn't exist`);
    }

    return this.labels[locale];
  }

  public setLabel(locale: string, label: string): LabelCollection {
    return LabelCollection.create({...this.labels, [locale]: label});
  }

  public normalize(): NormalizedLabelCollection {
    return this.labels;
  }
}

export const createLabelCollection = LabelCollection.create;
