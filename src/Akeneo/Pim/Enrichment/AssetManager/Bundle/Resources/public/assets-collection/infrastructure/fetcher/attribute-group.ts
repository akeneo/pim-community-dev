import promisify from 'akeneoassetmanager/tools/promisify';
import {AttributeGroupCollection} from 'akeneoassetmanager/platform/model/structure/attribute-group';
const fetcherRegistry = require('pim/fetcher-registry');

export const attributeGroupFetcher = () => fetcherRegistry.getFetcher('attribute-group');
export const fetchAssetAttributeGroups = (attributeGroupFetcher: any) => async (): Promise<
  AttributeGroupCollection
> => {
  return await promisify(attributeGroupFetcher.fetchAll());
};
