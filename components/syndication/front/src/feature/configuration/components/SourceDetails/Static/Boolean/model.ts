import {uuid} from 'akeneo-design-system';
import {BooleanReplacementOperation, isBooleanReplacementOperation} from '../../common';
import {Source} from '../../../../models';

type StaticBooleanOperations = {
  replacement?: BooleanReplacementOperation;
};

type StaticBooleanSource = {
  uuid: string;
  code: 'boolean';
  type: 'static';
  value: boolean;
  operations: StaticBooleanOperations;
  selection: {type: 'code'};
};

const getDefaultStaticBooleanSource = (): StaticBooleanSource => ({
  uuid: uuid(),
  code: 'boolean',
  type: 'static',
  value: false,
  operations: {},
  selection: {type: 'code'},
});

const isStaticBooleanOperations = (operations: Object): operations is StaticBooleanOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'replacement':
        return isBooleanReplacementOperation(operation);
      default:
        return false;
    }
  });

const isStaticBooleanSource = (source: Source): source is StaticBooleanSource =>
  'object' === typeof source &&
  null !== source &&
  undefined !== source.code &&
  'boolean' === source.code &&
  'type' in source.selection &&
  'code' === source.selection.type &&
  isStaticBooleanOperations(source.operations);

export {getDefaultStaticBooleanSource, isStaticBooleanSource};
export type {StaticBooleanSource};
