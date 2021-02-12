const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_add_words_to_locales_dictionaries';

const addWordsToLocalesDictionaries = async (locales: string[], words: string[]) => {
  await fetch(Routing.generate(ROUTE_NAME), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: `${JSON.stringify({
      locales,
      words,
    })}`,
  });
};

export {addWordsToLocalesDictionaries};
