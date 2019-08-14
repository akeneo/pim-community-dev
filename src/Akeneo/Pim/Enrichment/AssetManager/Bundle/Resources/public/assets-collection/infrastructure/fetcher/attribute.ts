import {Attribute} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import promisify from 'akeneoassetmanager/tools/promisify';
const fetcherRegistry = require('pim/fetcher-registry');

export const fetchAssetAttributes = async (): Promise<Attribute[]> => {
  return promisify(fetcherRegistry.getFetcher('attribute').fetchByTypes(['akeneo_asset_multiple_link']));
};
