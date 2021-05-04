const FetcherRegistry = require('pim/fetcher-registry');

const fetchNearestDueProject = async () => {
  return FetcherRegistry.getFetcher('project').search({search: null, options: {limit: 1, page: 1, completeness: 1}});
};

export {fetchNearestDueProject};
