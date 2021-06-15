import {LocaleCode} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';

const availableSeparators = [
  ',',
  ';',
  '|'
];

type SelectionSeparator = typeof availableSeparators[number];

type CodeLabelSelection =
  | {
  type: 'code';
}
  | {
  type: 'label';
  locale: LocaleCode;
};

type CodeLabelCollectionSelection =
  | {
    type: 'code';
    separator: SelectionSeparator;
  }
  | {
    type: 'label';
    locale: LocaleCode;
    separator: SelectionSeparator;
  };

type PriceCollectionSelection =
  | {
    type: 'amount';
  }
  | {
    type: 'currency';
  }
;

type MeasurementSelection =
  | {
    type: 'code';
  }
  | {
    type: 'label';
    locale: LocaleCode;
  }
  | {
    type: 'amount';
  }
;

type Selection = CodeLabelSelection | CodeLabelCollectionSelection | PriceCollectionSelection | MeasurementSelection;

const isSelectionSeparator = (separator: unknown) : separator is SelectionSeparator => typeof separator === 'string' && availableSeparators.includes(separator);

const getDefaultSelectionByAttribute = (attribute: Attribute): Selection => {
  switch (attribute.type) {
    case 'pim_catalog_price_collection':
      return {type: 'amount'};
    case 'pim_catalog_multiselect':
      return {type: 'code', separator: ','};
    default:
      return {type: 'code'};
  }
};

export {availableSeparators, isSelectionSeparator, getDefaultSelectionByAttribute};
export type {
  Selection,
  CodeLabelSelection,
  CodeLabelCollectionSelection,
  PriceCollectionSelection,
  MeasurementSelection,
  SelectionSeparator
};
