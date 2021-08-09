import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

const availableSeparators = {',': 'comma', ';': 'semicolon', '|': 'pipe'};

type PriceCollectionSeparator = keyof typeof availableSeparators;

const isPriceCollectionSeparator = (separator: unknown): separator is PriceCollectionSeparator =>
  typeof separator === 'string' && separator in availableSeparators;

type PriceCollectionSelection = {
  separator: PriceCollectionSeparator;
} & (
  | {
      type: 'currency_code';
    }
  | {
      type: 'currency_label';
      locale: LocaleCode;
    }
  | {
      type: 'amount';
    }
);

const isPriceCollectionSelection = (selection: any): selection is PriceCollectionSelection =>
  (selection.separator in availableSeparators &&
    'type' in selection &&
    ('currency_code' === selection.type || 'amount' === selection.type)) ||
  ('currency_label' === selection.type && 'locale' in selection);

type PriceCollectionOperations = {
  default_value?: DefaultValueOperation;
};

type PriceCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: PriceCollectionOperations;
  selection: PriceCollectionSelection;
};

const getDefaultPriceCollectionSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): PriceCollectionSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'amount', separator: ','},
});

const isPriceCollectionOperations = (operations: Object): operations is PriceCollectionOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isPriceCollectionSource = (source: Source): source is PriceCollectionSource =>
  isPriceCollectionSelection(source.selection) && isPriceCollectionOperations(source.operations);

export {
  availableSeparators,
  getDefaultPriceCollectionSource,
  isPriceCollectionSource,
  isPriceCollectionSelection,
  isPriceCollectionSeparator,
};
export type {PriceCollectionSource, PriceCollectionSelection};
