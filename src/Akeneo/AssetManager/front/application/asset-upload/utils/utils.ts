import Line, {
  LineErrorsByTarget,
  LineIdentifier,
  LineStatus,
} from 'akeneoassetmanager/application/asset-upload/model/line';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamily, getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/creation-asset';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';

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

export const sortLinesWithValidationErrorsFirst = (lines: Line[]): Line[] => {
  const invalidLines = lines.filter((line: Line) => lineHasAnError(line));
  const validLines = lines.filter((line: Line) => !lineHasAnError(line));

  return [...invalidLines, ...validLines];
};

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

export const createBasicValidationError = (message: string): ValidationError => {
  return {
    messageTemplate: message,
    parameters: {},
    message: message,
    propertyPath: '',
    invalidValue: null,
  };
};

export const assetUploadFailed = (lines: Line[], lineToUpdate: Line, errors: ValidationError[]): Line[] => {
  return lines.map((line: Line) => {
    if (line.id === lineToUpdate.id) {
      return {
        ...line,
        isFileUploading: false,
        isFileUploadFailed: true,
        errors: {
          ...line.errors,
          back: errors || [createBasicValidationError('pim_asset_manager.asset.upload.upload_failure')],
        },
      };
    }

    return line;
  });
};

export const assetCreationSucceeded = (lines: Line[], asset: CreationAsset): Line[] => {
  return lines.map((line: Line) =>
    line.code === asset.code ? {...line, assetCreated: true, isAssetCreating: false} : line
  );
};

export const selectLinesToSend = (lines: Line[]): Line[] =>
  lines.filter((line: Line) => !line.assetCreated && null !== line.file && !line.isFileUploading);

export const getCreatedAssetCodes = (lines: Line[]): AssetCode[] => {
  return lines.reduce(
    (assetCodes: AssetCode[], line: Line) =>
      line.assetCreated && !assetCodes.includes(line.code) ? [...assetCodes, line.code] : assetCodes,
    []
  );
};

export const getAllErrorsOfLineByTarget = (line: Line): LineErrorsByTarget => {
  let errors: LineErrorsByTarget = {
    common: [],
    code: [],
    channel: [],
    locale: [],
  };

  for (let error of getAllErrorsOfLine(line)) {
    switch (true) {
      case undefined === error.propertyPath:
        errors.common.push(error);
        break;
      case error.propertyPath === 'code':
        errors.code.push(error);
        break;
      case error.propertyPath.endsWith('.locale'):
        errors.locale.push(error);
        break;
      case error.propertyPath.endsWith('.channel'):
        errors.channel.push(error);
        break;
      default:
        errors.common.push(error);
        break;
    }
  }

  return errors;
};

export const getAllErrorsOfLine = (line: Line): ValidationError[] => [].concat.apply([], Object.values(line.errors));

const lineHasAnError = (line: Line): boolean => {
  return getAllErrorsOfLine(line).length > 0;
};

export const getStatusFromLine = (line: Line, valuePerLocale: boolean, valuePerChannel: boolean): LineStatus => {
  const isComplete: boolean =
    (!valuePerLocale || (valuePerLocale && line.locale !== null)) &&
    (!valuePerChannel || (valuePerChannel && line.channel !== null));

  if (lineHasAnError(line)) {
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
