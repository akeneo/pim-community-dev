export interface AttributeOption {
  code: string;
  id: number;
  optionValues: OptionValue;
  toImprove: undefined | 'n/a' | boolean;
}

export interface OptionValue {
  [localeCode: string]: {
    id: number;
    locale: string;
    value: string;
  };
}
