const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_check_title_suggestion';

const fetchTitleSuggestion = async (productId: number, channel: string, locale: string) => {
  const response = await fetch(Routing.generate(ROUTE_NAME), {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      Accept: "application/json"
    },
    body: `productId=${encodeURIComponent(productId)}&channel=${encodeURIComponent(channel)}&locale=${encodeURIComponent(locale)}`
  });

  const data = await response.json();

  if (data === {}) {
    return [];
  }

  return data;
};

export default fetchTitleSuggestion;
