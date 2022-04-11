import {LabelCollection} from '@akeneo-pim-community/shared';

type Unit = {
  code: string;
  labels: LabelCollection;
};

type MeasurementFamily = {
  code: string;
  units: Unit[];
};

export type {MeasurementFamily};
