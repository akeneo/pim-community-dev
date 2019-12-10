import Asset from 'akeneoassetmanager/domain/model/asset/asset';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';
import denormalizeAsset from 'akeneoassetmanager/application/denormalizer/asset';

export default (backendAsset: any): Asset => {
  const expectedKeys = ['identifier', 'asset_family_identifier', 'code', 'labels', 'image', 'values'];

  validateKeys(backendAsset, expectedKeys, 'The provided raw asset seems to be malformed.');

  return denormalizeAsset(backendAsset);
};
