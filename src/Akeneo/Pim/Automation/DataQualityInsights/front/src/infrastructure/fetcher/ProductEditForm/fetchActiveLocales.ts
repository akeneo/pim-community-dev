const FetcherRegistry = require('pim/fetcher-registry');

const fetchActiveLocales = async () => {
  return FetcherRegistry.getFetcher('locale').fetchActivated();
};

export default fetchActiveLocales;
