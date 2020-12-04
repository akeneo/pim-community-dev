export interface AttributeOption {
  code: string;
  id: number;
  optionValues: OptionValue;
}

export interface OptionValue {
  [localeCode: string]: {
    id: number;
    locale: string;
    value: string;
  };
}
