import Line, {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamily, getAssetFamilyMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
// import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
// import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import sanitize from 'akeneoassetmanager/tools/sanitize';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import {NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';

export const createLineFromFilename = (
  filename: string,
  assetFamily: AssetFamily
): Line => {
  const info = extractInfoFromFilename(filename, assetFamily);

  return {
    id: createUUIDV4(),
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

const extractInfoFromFilename = (
  filename: string,
  assetFamily: AssetFamily
) => {
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
    values: [
      ...asset.values,
      value,
    ],
  };
};

const addAsset = (assets: CreationAsset[], asset: CreationAsset): CreationAsset[] => {
  return [
    ...assets,
    asset,
  ];
};

export const createAssetsFromLines = (
  lines: Line[],
  assetFamily: AssetFamily
): CreationAsset[] => {
  return lines.reduce((assets: CreationAsset[], line: Line) => {
    const updatedAssets = assetExists(assets, line.code)
      ? assets
      : addAsset(assets, createEmptyAssetFromLine(line, assetFamily));

    return updatedAssets.map((asset: CreationAsset) => {
      if(asset.code !== line.code){
        return asset;
      }

      return addAssetValueToAsset(asset, createAssetValueFromLine(line, assetFamily));
    });
  }, []);
};

export const addLines = (
  lines: Line[],
  linesToAdd: Line[]
): Line[] => {
  return [
    ...linesToAdd,
    ...lines,
  ];
};

// export const sendAssets = (
//   assets: Asset[],
//   onSuccess: (asset: Asset) => void,
//   onError: (asset: Asset, errors: any) => void
// ) => {
//
// };

const createUUIDV4 = (): string => {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    let r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
};
