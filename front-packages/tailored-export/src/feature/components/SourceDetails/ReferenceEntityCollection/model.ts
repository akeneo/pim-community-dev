import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {CodeLabelCollectionSelection, isCodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

type ReferenceEntityCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
  selection: CodeLabelCollectionSelection;
};

const getDefaultReferenceEntityCollectionSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): ReferenceEntityCollectionSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isReferenceEntityCollectionSource = (source: Source): source is ReferenceEntityCollectionSource =>
  isCodeLabelCollectionSelection(source.selection);

export type {ReferenceEntityCollectionSource};
export {getDefaultReferenceEntityCollectionSource, isReferenceEntityCollectionSource};
