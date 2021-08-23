import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {CodeLabelCollectionSelection, DefaultValueOperation, isDefaultValueOperation} from '../common';

type GroupsOperations = {
  default_value?: DefaultValueOperation;
};

type GroupsSource = {
  uuid: string;
  code: 'groups';
  type: 'property';
  locale: null;
  channel: null;
  operations: GroupsOperations;
  selection: CodeLabelCollectionSelection;
};

const getDefaultGroupsSource = (): GroupsSource => ({
  uuid: uuid(),
  code: 'groups',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isGroupsOperations = (operations: Object): operations is GroupsOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isGroupsSource = (source: Source): source is GroupsSource =>
  'groups' === source.code && isGroupsOperations(source.operations);

export {getDefaultGroupsSource, isGroupsSource};
export type {GroupsSource};
