import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';
import {CodeLabelSelection} from '../common/CodeLabelSelector';

type FamilyOperations = {
  default_value?: DefaultValueOperation;
};

type FamilySource = {
  uuid: string;
  code: 'family';
  type: 'property';
  locale: null;
  channel: null;
  operations: FamilyOperations;
  selection: CodeLabelSelection;
};

const getDefaultFamilySource = (): FamilySource => ({
  uuid: uuid(),
  code: 'family',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isFamilyOperations = (operations: Object): operations is FamilyOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isFamilySource = (source: Source): source is FamilySource =>
  'family' === source.code && isFamilyOperations(source.operations);

export {isFamilySource, getDefaultFamilySource};
export type {FamilySource};
