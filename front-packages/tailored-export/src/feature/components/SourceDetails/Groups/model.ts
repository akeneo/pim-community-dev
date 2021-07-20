import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {CodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

type GroupsSource = {
  uuid: string;
  code: 'groups';
  type: 'property';
  locale: null;
  channel: null;
  operations: {};
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

const isGroupsSource = (source: Source): source is GroupsSource => 'groups' === source.code;

export {getDefaultGroupsSource, isGroupsSource};
export type {GroupsSource};
