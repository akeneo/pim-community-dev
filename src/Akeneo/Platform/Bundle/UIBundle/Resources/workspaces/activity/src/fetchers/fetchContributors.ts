const FetcherRegistry = require('pim/fetcher-registry');

type SearchContributorsParameters = {
  identifier: string;
  search: string;
  options: {
    limit: number;
    page: number;
  };
};

const fetchContributors = async (searchParameters: SearchContributorsParameters) => {
  return FetcherRegistry.getFetcher('contributor').search(searchParameters);
};

export {fetchContributors, SearchContributorsParameters};
