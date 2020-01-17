const FetcherRegistry = require('pim/fetcher-registry');

const fetchFamilies = async () => {
  return FetcherRegistry.getFetcher('family').fetchAll();
};

export default fetchFamilies;
