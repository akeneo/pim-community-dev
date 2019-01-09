export default interface Locale {
  code: string;
  label: string;
  region: string;
  language: string;
}

class InvalidTypeError extends Error {}

export interface NormalizedLocale {
  code: string;
  label: string;
  region: string;
  language: string;
}

export class ConcreteLocale {
  public constructor(
    readonly code: string,
    readonly label: string,
    readonly region: string,
    readonly language: string
  ) {
    if ('string' !== typeof code) {
      throw new InvalidTypeError('Locale expect a string as code to be created');
    }
    if ('string' !== typeof label) {
      throw new InvalidTypeError('Locale expect a string as label to be created');
    }
    if ('string' !== typeof region) {
      throw new InvalidTypeError('Locale expect a string as region to be created');
    }
    if ('string' !== typeof language) {
      throw new InvalidTypeError('Locale expect a string as language to be created');
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

export const createLocaleFromCode = (code: string): Locale => {
  if ('string' !== typeof code) {
    throw new InvalidTypeError(`CreateLocaleFromCode expect a string as parameter (${typeof code} given`);
  }

  const [language, region] = code.split('_');

  return new ConcreteLocale(code, code, region.toLowerCase(), language);
};
