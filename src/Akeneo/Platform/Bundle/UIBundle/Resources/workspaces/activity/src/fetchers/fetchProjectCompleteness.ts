const FetcherRegistry = require('pim/fetcher-registry');

const fetchProjectCompleteness = async (currentProjectCode: string, currentContributorUsername: string | null) => {
  return FetcherRegistry.getFetcher('project').getCompleteness(currentProjectCode, currentContributorUsername);
};

export {fetchProjectCompleteness};
