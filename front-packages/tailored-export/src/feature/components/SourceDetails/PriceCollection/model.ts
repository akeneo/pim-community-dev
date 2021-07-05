import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';

type PriceCollectionSelection = {
  type: 'amount' | 'currency';
  separator: ',';
};

const isPriceCollectionSelection = (selection: any): selection is PriceCollectionSelection =>
  'type' in selection && ('amount' === selection.type || 'currency' === selection.type) && 'separator' in selection;

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

export {getDefaultPriceCollectionSource, isPriceCollectionSource, isPriceCollectionSelection};
export type {PriceCollectionSource, PriceCollectionSelection};
