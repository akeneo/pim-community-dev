import {uuid} from 'akeneo-design-system';
import {Source} from '../../../../models';

type StaticMeasurementOperations = {};

type StaticMeasurementSource = {
  uuid: string;
  code: 'measurement';
  type: 'static';
  value: {
    value: string;
    unit: string;
  };
  operations: StaticMeasurementOperations;
  selection: {type: 'code'};
};

const getDefaultStaticMeasurementSource = (): StaticMeasurementSource => ({
  uuid: uuid(),
  code: 'measurement',
  type: 'static',
  value: {
    value: '',
    unit: '',
  },
  operations: {},
  selection: {type: 'code'},
});

const isStaticMeasurementOperations = (operations: Object): operations is StaticMeasurementOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      default:
        return false;
    }
  });

const isStaticMeasurementSource = (source: Source): source is StaticMeasurementSource =>
  'object' === typeof source &&
  null !== source &&
  undefined !== source.code &&
  'measurement' === source.code &&
  'type' in source.selection &&
  'code' === source.selection.type &&
  isStaticMeasurementOperations(source.operations);

export {getDefaultStaticMeasurementSource, isStaticMeasurementSource};
export type {StaticMeasurementSource};
