const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_get_locales_dictionary_info';

const fetchLocalesDictionaryInfo = async (locales: string[]) => {
  try {
    if (locales.length === 0) {
      return {};
    }

    const response = await fetch(Routing.generate(ROUTE_NAME), {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify({
        locales,
      }),
    });

    return await response.json();
  } catch (error) {
    console.error(error);
    return {};
  }
};

export {fetchLocalesDictionaryInfo};
