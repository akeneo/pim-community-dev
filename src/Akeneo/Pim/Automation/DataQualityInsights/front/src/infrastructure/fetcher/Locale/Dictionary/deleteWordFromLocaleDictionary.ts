const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_delete_locale_dictionary_word';

const deleteWordFromLocaleDictionary = async (wordId: number) => {
  await fetch(
    Routing.generate(ROUTE_NAME, {wordId}),
    {method: 'DELETE'},
  );
};

export {deleteWordFromLocaleDictionary};
