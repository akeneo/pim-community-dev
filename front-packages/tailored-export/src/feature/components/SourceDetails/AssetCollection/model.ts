import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {CodeLabelCollectionSelection, isCodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

type AssetCollectionOperations = {
  default_value?: DefaultValueOperation;
};

type AssetCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: AssetCollectionOperations;
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

const isAssetCollectionOperations = (operations: Object): operations is AssetCollectionOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isAssetCollectionSource = (source: Source): source is AssetCollectionSource =>
  isCodeLabelCollectionSelection(source.selection) && isAssetCollectionOperations(source.operations);

export type {AssetCollectionSource};
export {getDefaultAssetCollectionSource, isAssetCollectionSource};
