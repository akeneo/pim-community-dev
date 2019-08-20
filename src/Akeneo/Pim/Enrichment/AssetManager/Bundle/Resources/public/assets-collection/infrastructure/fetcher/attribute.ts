import promisify from 'akeneoassetmanager/tools/promisify';
import {Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
const fetcherRegistry = require('pim/fetcher-registry');

export const fetchAssetAttributes = async (): Promise<Attribute[]> => {
  return promisify(fetcherRegistry.getFetcher('attribute').fetchByTypes(['akeneo_asset_multiple_link']));
};
