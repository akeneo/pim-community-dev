import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';

const availableSeparators = {'.': 'dot', ',': 'comma', '٫‎': 'arabic_comma'};

type NumberSeparator = keyof typeof availableSeparators;
type NumberSelection = {decimal_separator: NumberSeparator};

type NumberSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
  selection: NumberSelection;
};

const getDefaultNumberSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): NumberSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {decimal_separator: '.'},
});

const isNumberSelection = (selection: any): selection is NumberSelection => 'decimal_separator' in selection;
const isNumberSource = (source: Source): source is NumberSource => isNumberSelection(source.selection);
const isNumberSeparator = (separator: any): separator is NumberSeparator => separator in availableSeparators;

export type {NumberSource, NumberSelection};
export {getDefaultNumberSource, isNumberSource, isNumberSeparator, availableSeparators};
