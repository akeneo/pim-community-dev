import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';

type MeasurementSelection =
  | {
      type: 'unit_code';
    }
  | {
      type: 'unit_label';
      locale: LocaleCode;
    }
  | {
      type: 'amount';
    };

const isMeasurementSelection = (selection: any): selection is MeasurementSelection =>
  'type' in selection &&
  (selection.type === 'unit_code' ||
    ('unit_label' === selection.type && 'locale' in selection) ||
    'amount' === selection.type);

type MeasurementSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
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

const isMeasurementSource = (source: Source): source is MeasurementSource => isMeasurementSelection(source.selection);

export type {MeasurementSelection, MeasurementSource};
export {isMeasurementSelection, getDefaultMeasurementSource, isMeasurementSource};
