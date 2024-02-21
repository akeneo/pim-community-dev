import {AttributeGroupCollection} from '../../../domain';

const FetcherRegistry = require('pim/fetcher-registry');

const fetchAttributeGroupsByCode = async (groupCodes: string[]): Promise<AttributeGroupCollection> => {
  try {
    return FetcherRegistry.getFetcher('attribute-group').search({
      identifiers: groupCodes.join(','),
      apply_filters: false,
    });
  } catch (error) {
    console.error(error);
    return Promise.resolve({});
  }
};

export {fetchAttributeGroupsByCode};
