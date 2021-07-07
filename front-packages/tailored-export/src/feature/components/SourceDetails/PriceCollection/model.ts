import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';

const availableSeparators = [',', ';', '|'];

type PriceCollectionSeparator = typeof availableSeparators[number];

const isPriceCollectionSeparator = (separator: unknown): separator is PriceCollectionSeparator =>
  typeof separator === 'string' && availableSeparators.includes(separator);

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
  (availableSeparators.includes(selection.separator) &&
    'type' in selection &&
    ('currency_code' === selection.type || 'amount' === selection.type)) ||
  ('currency_label' === selection.type && 'locale' in selection);

type PriceCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
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

const isPriceCollectionSource = (source: Source): source is PriceCollectionSource =>
  isPriceCollectionSelection(source.selection);

export {
  availableSeparators,
  getDefaultPriceCollectionSource,
  isPriceCollectionSource,
  isPriceCollectionSelection,
  isPriceCollectionSeparator,
};
export type {PriceCollectionSource, PriceCollectionSelection};
