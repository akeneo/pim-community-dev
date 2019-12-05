import Line, {LineStatus, Thumbnail} from 'akeneoassetmanager/application/asset-upload/model/line';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamily, getAssetFamilyMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import sanitize from 'akeneoassetmanager/tools/sanitize';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import {NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
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
    validation: {
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

export const addLines = (lines: Line[], linesToAdd: Line[]): Line[] => {
  return [...linesToAdd, ...lines];
};

export const addThumbnail = (lines: Line[], lineToUpdate: Line, thumbnail: Thumbnail): Line[] => {
  return lines.map((line: Line) => (line.id === lineToUpdate.id ? {...line, thumbnail} : line));
};

export const removeLine = (lines: Line[], lineToRemove: Line) =>
  lines.filter((line: Line) => line.id !== lineToRemove.id);

export const addUploadedFileToLine = (lines: Line[], lineToUpdate: Line, file: FileModel) => {
  return lines.map((line: Line) => (line.id === lineToUpdate.id ? {...line, file} : line));
};

export const updateUploadProgressToLine = (lines: Line[], lineToUpdate: Line, progress: number) => {
  return lines.map((line: Line) => (line.id === lineToUpdate.id ? {...line, uploadProgress: progress} : line));
};

const addBackValidationError = (line: Line, validation: ValidationError[]) => ({
  ...line,
  validation: {
    ...line.validation,
    back: validation,
  },
});

const assetCreationFailed = (lines: Line[], asset: CreationAsset, validation: ValidationError[]) => {
  return lines.map((line: Line) => (line.code === asset.code ? addBackValidationError(line, validation) : asset));
};

const assetCreationSucceeded = (lines: Line[], asset: CreationAsset) => {
  return lines.map((line: Line) => (line.code === asset.code ? {...asset, create: true} : asset));
};

const lineIsSendind = (lines: Line[], lineToSend: Line): Line[] =>
  lines.map((line: Line) => (lineToSend.id === line.id ? {...line, isSending: true} : line));

const assetIsSent = (lines: Line[], asset: CreationAsset): Line[] =>
  lines.map((line: Line) => (line.code === asset.code ? {...line, isSending: false} : line));

const selectLinesToSend = (lines: Line[]): Line[] =>
  lines.filter((line: Line) => !line.created && null !== line.file && !line.isSending);
const selectLinesCreated = (lines: Line[]): Line[] => lines.filter((line: Line) => line.created);

// export const sendAssets = (
//   assets: Asset[],
//   onSuccess: (asset: Asset) => void,
//   onError: (asset: Asset, errors: any) => void
// ) => {
//
// };

const createUUIDV4 = (): string => {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
    let r = (Math.random() * 16) | 0,
      v = c == 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
};

/*
to code: onUploadDown, onUploadError, removeLine


const lines = [];

user drops files:

const fileCollection = [];
const fileNames = fileCollection.map(file -> file.name);
startUpload(fileCollection, onUploadDown, onUploadError);
const newLines = filenames.map(filename => createLineFromFilename(filename, assetFamily));
const lines = addLines(lines, newLines);

user click on create

const linesToSend = selectLineToSend(lines);
const assets = createAssetsFromLines(linesToSend)
const lines = linesToSend.forEach((line: Line) => {
  return lineIsSending(lines, line)
}

assets.forEach(async (asset) => {
  const result = await createAsset(asset);

  if (null !== result) {
    assetCreationFailed(lines, asset, result);
  } else {
    assetCreationSucceeded(lines, asset)
  }
  assetIsSent(lines, asset)
})

*/
