const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_get_spellcheck_supported_locales';

const fetchSpellcheckSupportedLocales = async () => {
  const route = Routing.generate(ROUTE_NAME);

  const response = await fetch(route, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    },
  });

  return response.json();
};

export {fetchSpellcheckSupportedLocales};
