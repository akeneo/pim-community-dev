import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, DecimalSeparator, isDecimalSeparator, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

const availableRoundingTypes = ['no_rounding', 'standard', 'round_up', 'round_down'] as const;

type MeasurementSelection =
  | {
      type: 'unit_code';
    }
  | {
      type: 'unit_symbol';
    }
  | {
      type: 'unit_label';
      locale: LocaleCode;
    }
  | {
      type: 'value';
      decimal_separator?: DecimalSeparator;
    }
  | {
      type: 'value_and_unit_label';
      decimal_separator: DecimalSeparator;
      locale: LocaleCode;
    }
  | {
      type: 'value_and_unit_symbol';
      decimal_separator: DecimalSeparator;
    };

const isMeasurementSelection = (selection: any): selection is MeasurementSelection => {
  if (!('type' in selection)) return false;

  return (
    'unit_code' === selection.type ||
    'unit_symbol' === selection.type ||
    ('unit_label' === selection.type && 'locale' in selection) ||
    ('value' === selection.type && isDecimalSeparator(selection.decimal_separator)) ||
    ('value_and_unit_label' === selection.type &&
      'locale' in selection &&
      isDecimalSeparator(selection.decimal_separator)) ||
    ('value_and_unit_symbol' === selection.type && isDecimalSeparator(selection.decimal_separator))
  );
};

const getDefaultMeasurementSelection = (): MeasurementSelection => ({type: 'unit_code'});

const isDefaultMeasurementSelection = (selection?: MeasurementSelection): boolean => 'unit_code' === selection?.type;

type MeasurementConversionOperation = {
  type: 'measurement_conversion';
  target_unit_code: string | null;
};

const isMeasurementConversionOperation = (operation?: any): operation is MeasurementConversionOperation =>
  undefined !== operation &&
  'type' in operation &&
  'measurement_conversion' === operation.type &&
  'target_unit_code' in operation;

const getDefaultMeasurementConversionOperation = (): MeasurementConversionOperation => ({
  type: 'measurement_conversion',
  target_unit_code: null,
});

const isDefaultMeasurementConversionOperation = (operation?: MeasurementConversionOperation): boolean =>
  operation?.type === 'measurement_conversion' && operation.target_unit_code === null;

type RoundingType = 'standard' | 'no_rounding';
type MeasurementRoundingOperation = {
  type: 'measurement_rounding';
} & (
  | {
      rounding_type: 'no_rounding';
    }
  | {
      rounding_type: 'standard';
      precision: number;
    }
);
const DEFAULT_PRECISION = 2;
const MIN_PRECISION = 0;
const MAX_PRECISION = 12;

const isMeasurementRoundingOperation = (operation?: any): operation is MeasurementRoundingOperation =>
  undefined !== operation &&
  'type' in operation &&
  'measurement_rounding' === operation.type &&
  'rounding_type' in operation;

const getDefaultMeasurementRoundingOperation = (): MeasurementRoundingOperation => ({
  type: 'measurement_rounding',
  rounding_type: 'no_rounding',
});

const isDefaultMeasurementRoundingOperation = (operation?: MeasurementRoundingOperation): boolean =>
  operation?.type === 'measurement_rounding' && operation.rounding_type === 'no_rounding';

type MeasurementOperations = {
  default_value?: DefaultValueOperation;
  measurement_conversion?: MeasurementConversionOperation;
  measurement_rounding?: MeasurementRoundingOperation;
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
      case 'measurement_conversion':
        return isMeasurementConversionOperation(operation);
      case 'measurement_rounding':
        return isMeasurementRoundingOperation(operation);
      default:
        return false;
    }
  });

const isMeasurementSource = (source: Source): source is MeasurementSource =>
  isMeasurementSelection(source.selection) && isMeasurementOperations(source.operations);

export type {
  MeasurementSelection,
  MeasurementSource,
  MeasurementConversionOperation,
  MeasurementRoundingOperation,
  RoundingType,
};
export {
  availableRoundingTypes,
  getDefaultMeasurementSource,
  isDefaultMeasurementSelection,
  isMeasurementSelection,
  isMeasurementSource,
  isDefaultMeasurementConversionOperation,
  getDefaultMeasurementConversionOperation,
  isMeasurementConversionOperation,
  isDefaultMeasurementRoundingOperation,
  getDefaultMeasurementRoundingOperation,
  isMeasurementRoundingOperation,
  DEFAULT_PRECISION,
  MIN_PRECISION,
  MAX_PRECISION,
};
