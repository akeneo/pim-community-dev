import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {CodeLabelCollectionSelection, isCodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

type MultiSelectSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
  selection: CodeLabelCollectionSelection;
};

const getDefaultMultiSelectSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): MultiSelectSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isMultiSelectSource = (source: Source): source is MultiSelectSource =>
  isCodeLabelCollectionSelection(source.selection);

export {getDefaultMultiSelectSource, isMultiSelectSource};
export type {MultiSelectSource};
