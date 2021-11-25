import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

const availableDecimalSeparators = {'.': 'dot', ',': 'comma', '٫‎': 'arabic_comma'};

type NumberSeparator = keyof typeof availableDecimalSeparators;
type NumberSelection = {decimal_separator: NumberSeparator};

const getDefaultNumberSelection = (): NumberSelection => ({decimal_separator: '.'});

const isDefaultNumberSelection = (selection?: NumberSelection): boolean => '.' === selection?.decimal_separator;

type NumberOperations = {
  default_value?: DefaultValueOperation;
};

type NumberSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: NumberOperations;
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
  selection: getDefaultNumberSelection(),
});

const isNumberOperations = (operations: Object): operations is NumberOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isNumberSelection = (selection: any): selection is NumberSelection => 'decimal_separator' in selection;
const isNumberDecimalSeparator = (separator: any): separator is NumberSeparator =>
  separator in availableDecimalSeparators;

const isNumberSource = (source: Source): source is NumberSource =>
  isNumberSelection(source.selection) && isNumberOperations(source.operations);

export type {NumberSource, NumberSelection};
export {
  availableDecimalSeparators,
  getDefaultNumberSource,
  isDefaultNumberSelection,
  isNumberDecimalSeparator,
  isNumberSource,
};
