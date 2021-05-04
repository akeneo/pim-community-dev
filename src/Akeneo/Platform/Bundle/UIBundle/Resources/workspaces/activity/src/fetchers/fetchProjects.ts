const FetcherRegistry = require('pim/fetcher-registry');

type SearchProjectsParameters = {
  search: string;
  options: {
    limit: number;
    page: number;
    completeness: number;
  };
};

const fetchProjects = async (searchParameters: SearchProjectsParameters) => {
  return FetcherRegistry.getFetcher('project').search(searchParameters);
};

export {fetchProjects, SearchProjectsParameters};
