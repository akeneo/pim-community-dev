import Line, {LineStatus, Thumbnail, LineIdentifier} from 'akeneoassetmanager/application/asset-upload/model/line';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamily, getAssetFamilyMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import sanitize from 'akeneoassetmanager/tools/sanitize';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import {NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';
import {NormalizedValidationError as ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';

export const createLineFromFilename = (filename: string, assetFamily: AssetFamily): Line => {
  const info = extractInfoFromFilename(filename, assetFamily);

  return {
    id: createUUIDV4(),
    thumbnail: null,
    created: false,
    isSending: false,
    file: null,
    filename: filename,
    code: sanitize(info.code),
    locale: info.locale,
    channel: info.channel,
    status: LineStatus.WaitingForUpload,
    uploadProgress: null,
    errors: {
      back: [],
    },
  };
};

const extractInfoFromFilename = (filename: string, assetFamily: AssetFamily) => {
  const attribute = getAssetFamilyMainMedia(assetFamily) as NormalizedAttribute;
  const localizable = attribute.value_per_locale;
  const scopable = attribute.value_per_channel;
  let matches;

  if (localizable && scopable && (matches = filename.match(/^(\w+)-(\w*)-(\w*)/))) {
    return {
      code: matches[1],
      locale: matches[2] ? matches[2] : null,
      channel: matches[3] ? matches[3] : null,
    };
  }

  if (localizable && !scopable && (matches = filename.match(/^(\w+)-(\w+)/))) {
    return {
      code: matches[1],
      locale: matches[2],
      channel: null,
    };
  }

  if (!localizable && scopable && (matches = filename.match(/^(\w+)-(\w+)/))) {
    return {
      code: matches[1],
      locale: null,
      channel: matches[2],
    };
  }

  return {
    code: filename.replace(/\.[^/.]+$/, ''),
    locale: null,
    channel: null,
  };
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

const createAssetValueFromLine = (line: Line, assetFamily: AssetFamily): NormalizedMinimalValue => {
  return {
    attribute: assetFamily.attributeAsMainMedia,
    channel: line.channel,
    locale: line.locale,
    data: line.file,
  };
};

const addAssetValueToAsset = (asset: CreationAsset, value: NormalizedMinimalValue): CreationAsset => {
  return {
    ...asset,
    values: [...asset.values, value],
  };
};

const addAsset = (assets: CreationAsset[], asset: CreationAsset): CreationAsset[] => {
  return [...assets, asset];
};

export const createAssetsFromLines = (lines: Line[], assetFamily: AssetFamily): CreationAsset[] => {
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

export const updateUploadProgressToLine = (lines: Line[], lineToUpdate: Line, uploadProgress: number): Line[] => {
  return updateLine(lines, lineToUpdate.id, {
    uploadProgress,
    isSending: true,
  });
};

const addBackValidationError = (line: Line, errors: ValidationError[]): Line => ({
  ...line,
  errors: {
    ...line.errors,
    back: errors,
  },
});

export const assetCreationFailed = (lines: Line[], asset: CreationAsset, errors: ValidationError[]): Line[] => {
  return lines.map((line: Line) => (line.code === asset.code ? addBackValidationError(line, errors) : line));
};

export const assetCreationSucceeded = (lines: Line[], asset: CreationAsset): Line[] => {
  return lines.map((line: Line) => (line.code === asset.code ? {...line, create: true} : line));
};

export const lineCreationStart = (lines: Line[], lineToSend: Line): Line[] =>
  updateLine(lines, lineToSend.id, {isSending: true});

export const assetIsSent = (lines: Line[], asset: CreationAsset): Line[] =>
  lines.map((line: Line) => (line.code === asset.code ? {...line, isSending: false} : line));

export const selectLinesToSend = (lines: Line[]): Line[] =>
  lines.filter((line: Line) => !line.created && null !== line.file && !line.isSending);
export const selectLinesCreated = (lines: Line[]): Line[] => lines.filter((line: Line) => line.created);

export const getStatusFromLine = (line: Line, localizable: boolean, scopable: boolean): LineStatus => {
  const errorsCount = Object.values(line.errors).reduce((count: number, errors: ValidationError[]) => {
    return count + errors.length;
  }, 0);
  const isComplete: boolean =
    (!localizable || (localizable && line.locale !== null)) && (!scopable || (scopable && line.channel !== null));

  if (errorsCount > 0) {
    return LineStatus.Invalid;
  }
  if (!line.created && !line.isSending && line.file === null) {
    return LineStatus.WaitingForUpload;
  }
  if (line.isSending) {
    return LineStatus.UploadInProgress;
  }
  if (line.file !== null && !isComplete) {
    return LineStatus.Uploaded;
  }
  if (line.file !== null && isComplete) {
    return LineStatus.Valid;
  }
  if (line.created) {
    return LineStatus.Created;
  }

  throw Error('Unsupported state for upload line');
};

/**
 * Misc
 */
const createUUIDV4 = (): string => {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
    let r = (Math.random() * 16) | 0,
      v = c == 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
};
