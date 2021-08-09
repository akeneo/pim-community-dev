import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {CodeLabelSelection} from '../common/CodeLabelSelector';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

type FamilyVariantOperations = {
  default_value?: DefaultValueOperation;
};

type FamilyVariantSource = {
  uuid: string;
  code: 'family_variant';
  type: 'property';
  locale: null;
  channel: null;
  operations: FamilyVariantOperations;
  selection: CodeLabelSelection;
};

const getDefaultFamilyVariantSource = (): FamilyVariantSource => ({
  uuid: uuid(),
  code: 'family_variant',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isFamilyVariantOperations = (operations: Object): operations is FamilyVariantOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isFamilyVariantSource = (source: Source): source is FamilyVariantSource =>
  'family_variant' === source.code && isFamilyVariantOperations(source.operations);

export {isFamilyVariantSource, getDefaultFamilyVariantSource};
export type {FamilyVariantSource};
