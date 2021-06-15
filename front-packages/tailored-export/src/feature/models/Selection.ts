import {LocaleCode} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';

const availableSeparators = [',', ';', '|'];

type CollectionSeparator = typeof availableSeparators[number];

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
      separator: CollectionSeparator;
    }
  | {
      type: 'label';
      locale: LocaleCode;
      separator: CollectionSeparator;
    };

type PriceCollectionSelection =
  | {
      type: 'amount';
    }
  | {
      type: 'currency';
    };

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
    };

type Selection = CodeLabelSelection | CodeLabelCollectionSelection | PriceCollectionSelection | MeasurementSelection;

const isCollectionSeparator = (separator: unknown): separator is CollectionSeparator =>
  typeof separator === 'string' && availableSeparators.includes(separator);

const getDefaultSelectionByAttribute = (attribute: Attribute): Selection => {
  switch (attribute.type) {
    case 'pim_catalog_price_collection':
      return {type: 'amount'};
    case 'akeneo_reference_entity_collection':
    case 'pim_catalog_asset_collection':
    case 'pim_catalog_multiselect':
      return {type: 'code', separator: ','};
    default:
      return {type: 'code'};
  }
};

export {availableSeparators, isCollectionSeparator, getDefaultSelectionByAttribute};
export type {
  CodeLabelCollectionSelection,
  CodeLabelSelection,
  MeasurementSelection,
  PriceCollectionSelection,
  Selection,
};
