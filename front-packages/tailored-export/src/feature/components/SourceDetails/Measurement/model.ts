import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

const availableDecimalSeparators = {'.': 'dot', ',': 'comma', '٫‎': 'arabic_comma'};

type MeasurementDecimalSeparator = keyof typeof availableDecimalSeparators;

type MeasurementSelection =
  | {
      type: 'unit_code';
    }
  | {
      type: 'unit_label';
      locale: LocaleCode;
    }
  | {
      type: 'value';
      decimal_separator?: MeasurementDecimalSeparator;
    };

const isMeasurementDecimalSeparator = (separator?: string): separator is MeasurementDecimalSeparator =>
  undefined === separator || separator in availableDecimalSeparators;

const isMeasurementSelection = (selection: any): selection is MeasurementSelection => {
  if (!('type' in selection)) return false;

  return (
    'unit_code' === selection.type ||
    ('unit_label' === selection.type && 'locale' in selection) ||
    ('value' === selection.type && isMeasurementDecimalSeparator(selection.decimal_separator))
  );
};

const getDefaultMeasurementSelection = (): MeasurementSelection => ({type: 'unit_code'});

const isDefaultMeasurementSelection = (selection?: MeasurementSelection): boolean => 'unit_code' === selection?.type;

type MeasurementOperations = {
  default_value?: DefaultValueOperation;
};

type MeasurementSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: MeasurementOperations;
  selection: MeasurementSelection;
};

const getDefaultMeasurementSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): MeasurementSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: getDefaultMeasurementSelection(),
});

const isMeasurementOperations = (operations: Object): operations is MeasurementOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isMeasurementSource = (source: Source): source is MeasurementSource =>
  isMeasurementSelection(source.selection) && isMeasurementOperations(source.operations);

export type {MeasurementSelection, MeasurementSource};
export {
  availableDecimalSeparators,
  getDefaultMeasurementSource,
  isDefaultMeasurementSelection,
  isMeasurementDecimalSeparator,
  isMeasurementSelection,
  isMeasurementSource,
};
