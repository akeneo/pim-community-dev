import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {CodeLabelCollectionSelection, isCodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

type AssetCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
  selection: CodeLabelCollectionSelection;
};

const getDefaultAssetCollectionSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): AssetCollectionSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isAssetCollectionSource = (source: Source): source is AssetCollectionSource =>
  isCodeLabelCollectionSelection(source.selection);

export type {AssetCollectionSource};
export {getDefaultAssetCollectionSource, isAssetCollectionSource};
