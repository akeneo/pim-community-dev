import {uuid} from 'akeneo-design-system';
import {Source} from '../../../../models';

type StaticStringOperations = {};

type StaticStringSource = {
  uuid: string;
  code: 'string';
  type: 'static';
  value: string;
  operations: StaticStringOperations;
  selection: {type: 'code'};
};

const getDefaultStaticStringSource = (): StaticStringSource => ({
  uuid: uuid(),
  code: 'string',
  type: 'static',
  value: '',
  operations: {},
  selection: {type: 'code'},
});

const isStaticStringOperations = (operations: Object): operations is StaticStringOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      default:
        return false;
    }
  });

const isStaticStringSource = (source: Source): source is StaticStringSource =>
  'object' === typeof source &&
  null !== source &&
  undefined !== source.code &&
  'string' === source.code &&
  'type' in source.selection &&
  'code' === source.selection.type &&
  isStaticStringOperations(source.operations);

export {getDefaultStaticStringSource, isStaticStringSource};
export type {StaticStringSource};
