const FetcherRegistry = require('pim/fetcher-registry');

const fetchFamiliesByCodes = async (familyCodes: string[]) => {
  return FetcherRegistry.getFetcher('family').fetchByIdentifiers(familyCodes);
};

export default fetchFamiliesByCodes;
