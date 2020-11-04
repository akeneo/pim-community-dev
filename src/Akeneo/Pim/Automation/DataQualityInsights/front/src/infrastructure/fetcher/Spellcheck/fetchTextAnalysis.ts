const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_check_text';

const fetchTextAnalysis = async (text: string, locale: string) => {
  const response = await fetch(Routing.generate(ROUTE_NAME), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: `text=${encodeURIComponent(text)}&locale=${encodeURIComponent(locale)}`,
  });

  const data = await response.json();

  if (data === {} || !Array.isArray(data)) {
    return [];
  }

  return data;
};

export default fetchTextAnalysis;
