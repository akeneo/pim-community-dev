import {LocaleCode} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';

type Selection =
  | {
      type: 'code';
    }
  | {
      type: 'amount';
    }
  | {
      type: 'currency';
    }
  | {
      type: 'label';
      locale: LocaleCode;
    };

const getDefaultSelectionByAttribute = (attribute: Attribute): Selection => {
  switch (attribute.type) {
    case 'pim_catalog_price_collection':
      return {type: 'amount'};
    default:
      return {type: 'code'};
  }
};

export {getDefaultSelectionByAttribute};
export type {Selection};
