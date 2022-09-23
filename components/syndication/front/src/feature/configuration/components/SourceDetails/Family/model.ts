import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {
  DefaultValueOperation,
  isDefaultValueOperation,
  CodeLabelSelection,
  getDefaultCodeLabelSelection,
  ExtractOperation,
  isExtractOperation,
} from '../common';

type FamilyOperations = {
  default_value?: DefaultValueOperation;
  extract?: ExtractOperation;
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
  selection: getDefaultCodeLabelSelection(),
});

const isFamilyOperations = (operations: Object): operations is FamilyOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      case 'extract':
        return isExtractOperation(operation);
      default:
        return false;
    }
  });

const isFamilySource = (source: Source): source is FamilySource =>
  'family' === source.code && isFamilyOperations(source.operations);

export {isFamilySource, getDefaultFamilySource};
export type {FamilySource};
