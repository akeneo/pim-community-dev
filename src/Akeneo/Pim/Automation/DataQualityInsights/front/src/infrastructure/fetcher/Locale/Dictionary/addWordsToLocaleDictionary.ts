const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_add_words_to_dictionary';

const addWordsToLocaleDictionary = async (localeCode: string, words: string[]) => {
  await fetch(Routing.generate(ROUTE_NAME, {localeCode}), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: `${JSON.stringify(words)}`,
  });
};

export {addWordsToLocaleDictionary};
