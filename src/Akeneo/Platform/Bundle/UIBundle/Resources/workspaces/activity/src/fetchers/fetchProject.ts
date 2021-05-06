const FetcherRegistry = require('pim/fetcher-registry');

const fetchProject = async (projectCode: string) => {
  return FetcherRegistry.getFetcher('project').fetch(projectCode);
};

export {fetchProject};
