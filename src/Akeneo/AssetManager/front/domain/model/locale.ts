export type NormalizedLocaleCode = string;

export default interface Locale {
  code: NormalizedLocaleCode;
  label: string;
  region: string;
  language: string;
}

class InvalidTypeError extends Error {}

export interface NormalizedLocale {
  code: NormalizedLocaleCode;
  label: string;
  region: string;
  language: string;
}

export class ConcreteLocale {
  public constructor(
    readonly code: NormalizedLocaleCode,
    readonly label: string,
    readonly region: string,
    readonly language: string
  ) {
    if ('string' !== typeof code) {
      throw new InvalidTypeError('Locale expects a string as code to be created');
    }
    if ('string' !== typeof label) {
      throw new InvalidTypeError('Locale expects a string as label to be created');
    }
    if ('string' !== typeof region) {
      throw new InvalidTypeError('Locale expects a string as region to be created');
    }
    if ('string' !== typeof language) {
      throw new InvalidTypeError('Locale expects a string as language to be created');
    }

    Object.freeze(this);
  }
}

export const denormalizeLocale = (normalizedLocale: NormalizedLocale): Locale => {
  return new ConcreteLocale(
    normalizedLocale.code,
    normalizedLocale.label,
    normalizedLocale.region,
    normalizedLocale.language
  );
};

export const createLocaleFromCode = (code: NormalizedLocaleCode): Locale => {
  if ('string' !== typeof code) {
    throw new InvalidTypeError(`CreateLocaleFromCode expects a string as parameter (${typeof code} given`);
  }

  const [language, region] = code.split('_');

  return new ConcreteLocale(code, code, region.toLowerCase(), language);
};
