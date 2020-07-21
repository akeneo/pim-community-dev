const FetcherRegistry = require('pim/fetcher-registry');

const fetchFamilies = async () => {
  return FetcherRegistry.getFetcher('family').fetchAll({options: {expanded:0}});
};

export default fetchFamilies;
