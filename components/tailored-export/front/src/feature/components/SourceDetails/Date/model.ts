import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

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

type DateOperations = {
  default_value?: DefaultValueOperation;
};

type DateFormat = typeof availableDateFormats[number];

type DateSelection = {
  format: DateFormat;
};

const isDateFormat = (dateFormat: unknown): dateFormat is DateFormat =>
  typeof dateFormat === 'string' && availableDateFormats.includes(dateFormat);

const isDateSelection = (selection: any): selection is DateSelection =>
  'object' === typeof selection && null !== selection && 'format' in selection && isDateFormat(selection.format);

const getDefaultDateSelection = (): DateSelection => ({format: availableDateFormats[0]});

const isDefaultDateSelection = (selection?: DateSelection): boolean => selection?.format === availableDateFormats[0];

type DateSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: DateOperations;
  selection: DateSelection;
};

const getDefaultDateSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): DateSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: getDefaultDateSelection(),
});

const isDateOperations = (operations: Object): operations is DateOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isDateSource = (source: Source): source is DateSource =>
  isDateSelection(source.selection) && isDateOperations(source.operations);

const formatDateFormat = (dateFormat: DateFormat): string => {
  let formattedDate = dateFormat;

  if (formattedDate.includes('yyyy')) {
    formattedDate = formattedDate.replace('yyyy', '1998');
  }

  if (formattedDate.includes('yy')) {
    formattedDate = formattedDate.replace('yy', '98');
  }

  if (formattedDate.includes('mm')) {
    formattedDate = formattedDate.replace('mm', '07');
  }

  if (formattedDate.includes('m')) {
    formattedDate = formattedDate.replace('m', '7');
  }

  formattedDate = formattedDate.replace('dd', '13');

  return `${dateFormat} (${formattedDate})`;
};

export {
  getDefaultDateSource,
  isDateSource,
  isDateFormat,
  availableDateFormats,
  isDefaultDateSelection,
  formatDateFormat,
};
export type {DateSelection, DateSource};
