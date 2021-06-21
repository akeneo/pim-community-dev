import {LocaleCode} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';

const availableSeparators = [',', ';', '|'];

type CollectionSeparator = typeof availableSeparators[number];

const availableDateFormats = [
  'yyyy-mm-dd',
  'yyyy/mm/dd',
  'yyyy.mm.dd',
  'yy.m.dd',
  'mm-dd-yyyy',
  'mm/dd/yyyy',
  'mm.dd.yyyy',
  'dd-mm-yyyy',
  'dd/mm/yyyy',
  'dd.mm.yyyy',
  'dd-mm-yy',
  'dd.mm.yy',
  'dd/mm/yy',
  'dd-m-yy',
  'dd/m/yy',
  'dd.m.yy',
];

type DateFormat = typeof availableDateFormats[number];

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

type DateSelection = {
  format: DateFormat;
};

type Selection =
  | CodeLabelSelection
  | CodeLabelCollectionSelection
  | PriceCollectionSelection
  | MeasurementSelection
  | DateSelection;

const isCollectionSeparator = (separator: unknown): separator is CollectionSeparator =>
  typeof separator === 'string' && availableSeparators.includes(separator);

const isDateFormat = (dateFormat: unknown): dateFormat is DateFormat =>
  typeof dateFormat === 'string' && availableDateFormats.includes(dateFormat);

const getDefaultSelectionByAttribute = (attribute: Attribute): Selection => {
  switch (attribute.type) {
    case 'pim_catalog_price_collection':
      return {type: 'amount'};
    case 'akeneo_reference_entity_collection':
    case 'pim_catalog_asset_collection':
    case 'pim_catalog_multiselect':
      return {type: 'code', separator: ','};
    case 'pim_catalog_date':
      return {format: 'yyyy-mm-dd'};
    default:
      return {type: 'code'};
  }
};

export {availableDateFormats, availableSeparators, isCollectionSeparator, isDateFormat, getDefaultSelectionByAttribute};
export type {
  CodeLabelCollectionSelection,
  CodeLabelSelection,
  DateSelection,
  MeasurementSelection,
  PriceCollectionSelection,
  Selection,
};
