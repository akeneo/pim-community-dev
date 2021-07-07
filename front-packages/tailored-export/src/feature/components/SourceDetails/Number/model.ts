import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';

const availableSeparators = ['.', ',', '٫‎'];

type NumberSeparator = typeof availableSeparators[number];
type NumberSelection = {separator: NumberSeparator};

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
  selection: {separator: availableSeparators[0]},
});

const isNumberSelection = (selection: any): selection is NumberSelection => 'separator' in selection;
const isNumberSource = (source: Source): source is NumberSource => isNumberSelection(source.selection);
const isNumberSeparator = (separator: any): separator is NumberSeparator => availableSeparators.includes(separator);

export type {NumberSource, NumberSelection};
export {getDefaultNumberSource, isNumberSource, isNumberSeparator, availableSeparators};
