import Line, {
  LineErrorsByTarget,
  LineIdentifier,
  LineStatus,
} from 'akeneoassetmanager/application/asset-upload/model/line';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamily, getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import sanitize from 'akeneoassetmanager/tools/sanitize';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/creation-asset';
import {NormalizedValidationError as ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {createUUIDV4} from 'akeneoassetmanager/application/asset-upload/utils/uuid';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import Locale from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';

type FilenameInfo = {
  code: string;
  locale: string | null;
  channel: string | null;
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
    file: null,
    filename: filename,
    code: info.code,
    locale: info.locale,
    channel: info.channel,
    uploadProgress: null,
    errors: {
      back: [],
    },
  };
};

const extractInfoFromFilename = (filename: string, assetFamily: AssetFamily): FilenameInfo => {
  const attribute = getAttributeAsMainMedia(assetFamily) as NormalizedAttribute;
  const valuePerLocale = attribute.value_per_locale;
  const valuePerChannel = attribute.value_per_channel;
  let matches;

  if (valuePerLocale && valuePerChannel && (matches = filename.match(/^(\w+)-(\w*)-(\w*)/))) {
    return {
      code: sanitize(matches[1]),
      locale: matches[2] ? matches[2] : null,
      channel: matches[3] ? matches[3] : null,
    };
  }

  if (valuePerLocale && !valuePerChannel && (matches = filename.match(/^(\w+)-(\w+)/))) {
    return {
      code: sanitize(matches[1]),
      locale: matches[2],
      channel: null,
    };
  }

  if (!valuePerLocale && valuePerChannel && (matches = filename.match(/^(\w+)-(\w+)/))) {
    return {
      code: sanitize(matches[1]),
      locale: null,
      channel: matches[2],
    };
  }

  return extractInfoWithOnlyCodeFromFilename(filename);
};

const extractInfoWithOnlyCodeFromFilename = (filename: string): FilenameInfo => {
  return {
    code: sanitize(filename.replace(/\.[^/.]+$/, '')),
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

const findAssetByCode = (assets: CreationAsset[], code: AssetCode): CreationAsset | null => {
  return assets.find((asset: CreationAsset) => asset.code === code) || null;
};

const assetExists = (assets: CreationAsset[], code: AssetCode): boolean => {
  return null !== findAssetByCode(assets, code);
};

const createEmptyAssetFromLine = (line: Line, assetFamily: AssetFamily): CreationAsset => {
  return {
    assetFamilyIdentifier: assetFamily.identifier,
    code: line.code,
    labels: {},
    values: [],
  };
};

const createAssetValueFromLine = (line: Line, assetFamily: AssetFamily): EditionValue => {
  return {
    attribute: getAttributeAsMainMedia(assetFamily),
    channel: line.channel,
    locale: line.locale,
    data: line.file,
  };
};

const addAssetValueToAsset = (asset: CreationAsset, value: EditionValue): CreationAsset => {
  return {
    ...asset,
    values: [...asset.values, value],
  };
};

const addAsset = (assets: CreationAsset[], asset: CreationAsset): CreationAsset[] => {
  return [...assets, asset];
};

export const createCreationAssetsFromLines = (lines: Line[], assetFamily: AssetFamily): CreationAsset[] => {
  return lines.reduce((assets: CreationAsset[], line: Line) => {
    const updatedAssets = assetExists(assets, line.code)
      ? assets
      : addAsset(assets, createEmptyAssetFromLine(line, assetFamily));

    return updatedAssets.map((asset: CreationAsset) => {
      if (asset.code !== line.code) {
        return asset;
      }

      return addAssetValueToAsset(asset, createAssetValueFromLine(line, assetFamily));
    });
  }, []);
};

/*
 * Lines operations
 */
export const addLines = (lines: Line[], linesToAdd: Line[]): Line[] => {
  return [...linesToAdd, ...lines];
};

export const updateLine = (lines: Line[], lineToUpdateIdentifier: LineIdentifier, update: any): Line[] =>
  lines.map((line: Line): Line => (line.id === lineToUpdateIdentifier ? {...line, ...(update as Line)} : line));

export const removeLine = (lines: Line[], lineToRemove: Line): Line[] =>
  lines.filter((line: Line) => line.id !== lineToRemove.id);

const addBackValidationError = (line: Line, errors: ValidationError[]): Line => ({
  ...line,
  errors: {
    ...line.errors,
    back: errors,
  },
});

export const assetCreationFailed = (lines: Line[], asset: CreationAsset, errors: ValidationError[]): Line[] => {
  return lines.map((line: Line) =>
    line.code === asset.code
      ? {
          ...addBackValidationError(line, errors),
          isAssetCreating: false,
        }
      : line
  );
};

export const assetCreationSucceeded = (lines: Line[], asset: CreationAsset): Line[] => {
  return lines.map((line: Line) =>
    line.code === asset.code ? {...line, assetCreated: true, isAssetCreating: false} : line
  );
};

export const selectLinesToSend = (lines: Line[]): Line[] =>
  lines.filter((line: Line) => !line.assetCreated && null !== line.file && !line.isFileUploading);

export const getAllErrorsOfLineByTarget = (line: Line): LineErrorsByTarget => {
  let errors: LineErrorsByTarget = {
    all: [],
    code: [],
    channel: [],
    locale: [],
  };

  for (let error of getAllErrorsOfLine(line)) {
    switch (error.propertyPath) {
      case 'code':
        errors.code.push(error);
        break;
      default:
        errors.all.push(error);
        break;
    }
  }

  return errors;
};

export const getAllErrorsOfLine = (line: Line): ValidationError[] => [].concat.apply([], Object.values(line.errors));

export const getStatusFromLine = (line: Line, valuePerLocale: boolean, valuePerChannel: boolean): LineStatus => {
  const errorsCount = Object.values(line.errors).reduce((count: number, errors: ValidationError[]) => {
    return count + errors.length;
  }, 0);
  const isComplete: boolean =
    (!valuePerLocale || (valuePerLocale && line.locale !== null)) &&
    (!valuePerChannel || (valuePerChannel && line.channel !== null));

  if (errorsCount > 0) {
    return LineStatus.Invalid;
  }
  if (line.isFileUploading) {
    return LineStatus.UploadInProgress;
  }
  if (line.assetCreated) {
    return LineStatus.Created;
  }
  if (line.file !== null && !isComplete) {
    return LineStatus.Uploaded;
  }
  if (line.file !== null && isComplete) {
    return LineStatus.Valid;
  }

  return LineStatus.WaitingForUpload;
};

export const hasAnUnsavedLine = (lines: Line[], valuePerLocale: boolean, valuePerChannel: boolean): boolean => {
  return lines.reduce((isDirty: boolean, line: Line) => {
    if (isDirty) {
      return isDirty;
    }

    return LineStatus.Created !== getStatusFromLine(line, valuePerLocale, valuePerChannel);
  }, false);
};
