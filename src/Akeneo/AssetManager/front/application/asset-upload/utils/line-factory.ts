import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {AssetFamily, getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {sanitizeAssetCode} from 'akeneoassetmanager/tools/sanitizeAssetCode';
import {createUUIDV4} from 'akeneoassetmanager/application/asset-upload/utils/uuid';
import Locale from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';

type FilenameInfo = {
  code: string;
  locale: LocaleReference;
  channel: ChannelReference;
};

export const createLineFromFilename = (
  filename: string,
  assetFamily: AssetFamily,
  channels: Channel[],
  locales: Locale[]
): Line => {
  let info = extractInfoFromFilename(filename, assetFamily);

  if (!isFilenameInfoValid(info, channels, locales)) {
    info = extractInfoWithOnlyCodeFromFilename(filename);
  }

  return {
    id: createUUIDV4(),
    thumbnail: null,
    assetCreated: false,
    isAssetCreating: false,
    isFileUploading: false,
    isFileUploadFailed: false,
    file: null,
    filename: filename,
    code: info.code,
    locale: info.locale,
    channel: info.channel,
    uploadProgress: null,
    errors: {
      back: [],
      front: [],
    },
  };
};

const extractInfoFromFilename = (filename: string, assetFamily: AssetFamily): FilenameInfo => {
  const attribute = getAttributeAsMainMedia(assetFamily) as NormalizedAttribute;
  const valuePerLocale = attribute.value_per_locale;
  const valuePerChannel = attribute.value_per_channel;
  let matches;

  if (
    valuePerLocale &&
    valuePerChannel &&
    (matches = filename.match(/^(?<code>.+)-(?<locale>\w*)-(?<channel>\w*)(\.\w*)?$/))
  ) {
    return {
      code: sanitizeAssetCode(matches.groups?.code ?? ''),
      locale: matches.groups?.locale || null,
      channel: matches.groups?.channel || null,
    };
  }

  if (valuePerLocale && !valuePerChannel && (matches = filename.match(/^(?<code>.+)-(?<locale>\w*)(\.\w*)?$/))) {
    return {
      code: sanitizeAssetCode(matches.groups?.code ?? ''),
      locale: matches.groups?.locale || null,
      channel: null,
    };
  }

  if (!valuePerLocale && valuePerChannel && (matches = filename.match(/^(?<code>.+)-(?<channel>\w*)(\.\w*)?$/))) {
    return {
      code: sanitizeAssetCode(matches.groups?.code ?? ''),
      locale: null,
      channel: matches.groups?.channel || null,
    };
  }

  return extractInfoWithOnlyCodeFromFilename(filename);
};

const extractInfoWithOnlyCodeFromFilename = (filename: string): FilenameInfo => {
  return {
    code: sanitizeAssetCode(filename.replace(/\.[^/.]+$/, '')),
    locale: null,
    channel: null,
  };
};

const isFilenameInfoValid = (info: FilenameInfo, channels: Channel[], locales: Locale[]): boolean => {
  if (null === info.locale && null === info.channel) {
    return true;
  }

  if (null !== info.locale && null === info.channel) {
    return undefined !== locales.find((locale: Locale) => locale.code === info.locale);
  }

  if (null === info.locale && null !== info.channel) {
    return undefined !== channels.find((channel: Channel) => channel.code === info.channel);
  }

  const channel = channels.find((channel: Channel) => channel.code === info.channel);
  if (undefined === channel) {
    return false;
  }

  return undefined !== channel.locales.find((locale: Locale) => locale.code === info.locale);
};
