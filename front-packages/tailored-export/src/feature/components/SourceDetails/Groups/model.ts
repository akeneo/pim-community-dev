import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {CodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';
import {DefaultValueOperation, isDefaultValueOperation} from "../common";

type GroupOperations = {
  default_value?: DefaultValueOperation;
};

type GroupsSource = {
  uuid: string;
  code: 'groups';
  type: 'property';
  locale: null;
  channel: null;
  operations: GroupOperations;
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

const isGroupOperations = (operations: Object): operations is GroupOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isGroupsSource = (source: Source): source is GroupsSource =>
  'groups' === source.code && isGroupOperations(source.operations);

export {getDefaultGroupsSource, isGroupsSource};
export type {GroupsSource};
