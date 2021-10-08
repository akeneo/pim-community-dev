const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_get_attribute_spellcheck_evaluation';

const fetchSpellcheckEvaluation = async (attributeCode: string) => {
  const route = Routing.generate(ROUTE_NAME, {
    attributeCode,
  });

  const response = await fetch(route, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    },
  });

  if (response.ok) {
    return response.json();
  } else {
    //Can happen when DQI is deactivated
    return null;
  }
};

export default fetchSpellcheckEvaluation;
