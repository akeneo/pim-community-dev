export default interface Locale {
  code: string;
  label: string;
  region: string;
  language: string;
};

class ConcreteLocale {
  readonly code: string;
  readonly label: string;
  readonly region: string;
  readonly language: string;

  public constructor(code: string, label: string, region: string, language: string) {
    this.code = code;
    this.label = label;
    this.region = region;
    this.language = language;
  }
}

export const createLocale = (rawLocale: any): Locale => {
  return new ConcreteLocale(rawLocale.code, rawLocale.label, rawLocale.region, rawLocale.language);
};
