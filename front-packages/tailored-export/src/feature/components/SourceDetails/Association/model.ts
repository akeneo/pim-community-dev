import {uuid} from 'akeneo-design-system';
import {AssociationType, Source} from '../../../models';
import {CodeLabelCollectionSelection, isCodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

type AssociationSource = {
  uuid: string;
  code: string;
  type: 'association';
  locale: null;
  channel: null;
  operations: {};
  selection: CodeLabelCollectionSelection;
};

const getDefaultAssociationSource = (associationType: AssociationType): AssociationSource => ({
  uuid: uuid(),
  code: associationType.code,
  type: 'association',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isAssociationSource = (source: Source): source is AssociationSource =>
  source.type === 'association' && isCodeLabelCollectionSelection(source.selection);

export type {AssociationSource};
export {getDefaultAssociationSource, isAssociationSource};
