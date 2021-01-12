const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_get_locale_dictionary_words';

const fetchLocaleDictionary = async (localeCode: string, page: number, itemsPerPage: number, search: string) => {
  let routeParams = {
    localeCode,
    page,
    itemsPerPage,
    search,
  };

  const response = await fetch(Routing.generate(ROUTE_NAME, routeParams));

  return await response.json();
};

export {fetchLocaleDictionary};
