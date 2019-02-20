class InvalidTypeError extends Error {}

export type NormalizedLocaleReference = string | null;

export default class LocaleReference {
  private constructor(private localeReference: string | null) {
    if (!('string' === typeof localeReference || null === localeReference)) {
      throw new InvalidTypeError('LocaleReference expects a string or null as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(localeReference: string | null): LocaleReference {
    return new LocaleReference(localeReference);
  }

  public equals(localeReference: LocaleReference): boolean {
    return this.stringValue() === localeReference.stringValue();
  }

  public stringValue(): string {
    return null === this.localeReference ? '' : this.localeReference;
  }

  public isEmpty(): boolean {
    return null === this.localeReference;
  }

  public normalize(): string | null {
    return this.localeReference;
  }
}

export const createLocaleReference = LocaleReference.create;
export const denormalizeLocaleReference = LocaleReference.create;
