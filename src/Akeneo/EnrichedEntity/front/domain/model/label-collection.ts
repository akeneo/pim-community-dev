interface RawLabelCollection {
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
  private constructor(private labels: RawLabelCollection) {
    if ('object' !== typeof labels) {
      throw new InvalidTypeError('LabelCollection expect only values as {"en_US": "My label"} to be created');
    }

    Object.keys(labels).forEach((key: string) => {
      ensureString(labels[key]);
    });

    Object.freeze(this);
  }

  public static create(labels: RawLabelCollection): LabelCollection {
    return new LabelCollection(labels);
  }

  public hasLabel(locale: string): boolean {
    return 'string' === typeof this.labels[locale];
  }

  public getLabel(locale: string): string {
    if (!this.hasLabel(locale)) {
      throw new UnknownLocaleError(`The label for locale ${locale} doesn't exist`);
    }

    return this.labels[locale];
  }
}

export const createLabelCollection = LabelCollection.create;
