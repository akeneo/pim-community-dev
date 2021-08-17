import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

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
    };

const isMeasurementSelection = (selection: any): selection is MeasurementSelection =>
  'type' in selection &&
  ('unit_code' === selection.type ||
    ('unit_label' === selection.type && 'locale' in selection) ||
    'value' === selection.type);

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
  selection: {type: 'unit_code'},
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
export {isMeasurementSelection, getDefaultMeasurementSource, isMeasurementSource};
