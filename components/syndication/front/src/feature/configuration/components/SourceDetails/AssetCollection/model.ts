import {uuid} from 'akeneo-design-system';
import {
  Channel,
  ChannelReference,
  LocaleReference,
  LocaleCode,
  getLocalesFromChannel,
} from '@akeneo-pim-community/shared';
import {AssetFamily, Attribute, Source, getAttributeAsMainMedia, Target} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

const availableSeparators = {',': 'comma', ';': 'semicolon', '|': 'pipe'};

type CollectionSeparator = keyof typeof availableSeparators;

const isCollectionSeparator = (separator: unknown): separator is CollectionSeparator =>
  typeof separator === 'string' && separator in availableSeparators;

const ASSET_COLLECTION_MEDIA_FILE_URL_SELECTION_TYPE = 'media_file_url';
const ASSET_COLLECTION_MEDIA_FILE_SELECTION_TYPE = 'media_file';
const ASSET_COLLECTION_MEDIA_LINK_SELECTION_TYPE = 'media_link';

const propertyTypes = ['file_key', 'file_path', 'original_filename'];
type AssetCollectionMediaFileSelectionProperty = typeof propertyTypes[number];

const isValidAssetCollectionMediaFileSelectionProperty = (
  type: unknown
): type is AssetCollectionMediaFileSelectionProperty => {
  return 'string' === typeof type && propertyTypes.includes(type);
};

type AssetCollectionCodeSelection = {
  type: 'code';
  separator: CollectionSeparator;
};
type AssetCollectionMediaFileUrlSelection = {
  type: typeof ASSET_COLLECTION_MEDIA_FILE_URL_SELECTION_TYPE;
  position: number;
  locale: LocaleReference;
  channel: ChannelReference;
  separator: CollectionSeparator;
};

type AssetCollectionLabelSelection = {
  type: 'label';
  locale: LocaleCode;
  separator: CollectionSeparator;
};

type AssetCollectionMediaFileSelection = {
  type: typeof ASSET_COLLECTION_MEDIA_FILE_SELECTION_TYPE;
  locale: LocaleReference;
  channel: ChannelReference;
  property: AssetCollectionMediaFileSelectionProperty;
  separator: CollectionSeparator;
};

type AssetCollectionMediaLinkSelection = {
  type: typeof ASSET_COLLECTION_MEDIA_LINK_SELECTION_TYPE;
  locale: LocaleReference;
  channel: ChannelReference;
  with_prefix_and_suffix: boolean;
  separator: CollectionSeparator;
};

type AssetCollectionSelection =
  | AssetCollectionMediaFileUrlSelection
  | AssetCollectionCodeSelection
  | AssetCollectionLabelSelection
  | AssetCollectionMediaFileSelection
  | AssetCollectionMediaLinkSelection;

type AssetCollectionMainMediaSelection = AssetCollectionMediaFileSelection | AssetCollectionMediaLinkSelection;
type AssetCollectionMainMediaUrlSelection = AssetCollectionMediaFileUrlSelection;

const isAssetCollectionSelection = (selection: any): selection is AssetCollectionSelection => {
  if (
    !('type' in selection) ||
    ((!('separator' in selection) || !Object.keys(availableSeparators).includes(selection.separator)) &&
      selection.type !== ASSET_COLLECTION_MEDIA_FILE_URL_SELECTION_TYPE)
  ) {
    return false;
  }

  switch (selection.type) {
    case 'code':
      return true;
    case 'label':
      return 'locale' in selection && 'string' === typeof selection.locale;
    case 'media_file':
      return 'property' in selection && propertyTypes.includes(selection.property);
    case 'media_link':
      return 'with_prefix_and_suffix' in selection && 'boolean' === typeof selection.with_prefix_and_suffix;
    case 'media_file_url':
      return 'position' in selection && 'number' === typeof selection.position;
    default:
      return false;
  }
};

const isAssetCollectionMediaSelection = (selection: any): selection is AssetCollectionMainMediaSelection =>
  isAssetCollectionSelection(selection) && ('media_link' === selection.type || 'media_file' === selection.type);

const getDefaultAssetCollectionSelection = (target: Target): AssetCollectionSelection =>
  'url' === target.type
    ? {
        type: ASSET_COLLECTION_MEDIA_FILE_URL_SELECTION_TYPE,
        position: 0,
        locale: null,
        channel: null,
        separator: ',',
      }
    : {
        type: 'code',
        separator: ',',
      };

const isDefaultAssetCollectionSelection = (selection?: AssetCollectionSelection): boolean =>
  'code' === selection?.type && ',' === selection?.separator;

const getDefaultAssetCollectionMediaSelection = (
  assetFamily: AssetFamily,
  channels: Channel[]
): AssetCollectionSelection => {
  const attribute = getAttributeAsMainMedia(assetFamily);
  const channelReference = attribute.value_per_channel ? channels[0].code : null;
  const locales = getLocalesFromChannel(channels, channelReference);
  const localeReference = attribute.value_per_locale ? locales[0].code : null;

  switch (attribute.type) {
    case 'media_file':
      return {
        type: 'media_file',
        locale: localeReference,
        channel: channelReference,
        property: 'file_key',
        separator: ',',
      };
    case 'media_link':
      return {
        type: 'media_link',
        locale: localeReference,
        channel: channelReference,
        with_prefix_and_suffix: false,
        separator: ',',
      };
    default:
      throw new Error(`Unknown attribute type : "${attribute.type}"`);
  }
};

const getDefaultAssetCollectionMediaUrlSelection = (
  assetFamily: AssetFamily,
  channels: Channel[]
): AssetCollectionMediaFileUrlSelection => {
  const attribute = getAttributeAsMainMedia(assetFamily);
  const channelReference = attribute.value_per_channel ? channels[0].code : null;
  const locales = getLocalesFromChannel(channels, channelReference);
  const localeReference = attribute.value_per_locale ? locales[0].code : null;

  switch (attribute.type) {
    case 'media_file':
      return {
        type: 'media_file_url',
        locale: localeReference,
        channel: channelReference,
        position: 0,
        separator: ',',
      };
    default:
      throw new Error(`Unsupported attribute type : "${attribute.type}"`);
  }
};

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
  selection: AssetCollectionSelection;
};

const getDefaultAssetCollectionSource = (
  attribute: Attribute,
  target: Target,
  channel: ChannelReference,
  locale: LocaleReference
): AssetCollectionSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: getDefaultAssetCollectionSelection(target),
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
  isAssetCollectionSelection(source.selection) && isAssetCollectionOperations(source.operations);

export type {
  AssetCollectionSelection,
  AssetCollectionMainMediaSelection,
  AssetCollectionSource,
  AssetCollectionMainMediaUrlSelection,
  AssetCollectionMediaFileUrlSelection,
};
export {
  getDefaultAssetCollectionSource,
  isAssetCollectionSource,
  isAssetCollectionSelection,
  isAssetCollectionMediaSelection,
  isDefaultAssetCollectionSelection,
  getDefaultAssetCollectionSelection,
  getDefaultAssetCollectionMediaSelection,
  getDefaultAssetCollectionMediaUrlSelection,
  isCollectionSeparator,
  availableSeparators,
  ASSET_COLLECTION_MEDIA_LINK_SELECTION_TYPE,
  ASSET_COLLECTION_MEDIA_FILE_SELECTION_TYPE,
  ASSET_COLLECTION_MEDIA_FILE_URL_SELECTION_TYPE,
  isValidAssetCollectionMediaFileSelectionProperty,
};
