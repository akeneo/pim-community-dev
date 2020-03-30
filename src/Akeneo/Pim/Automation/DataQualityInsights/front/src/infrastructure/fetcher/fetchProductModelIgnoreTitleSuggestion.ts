const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_product_model_check_title_suggestion_ignore';

const fetchProductModelIgnoreTitleSuggestion = async (title: string, channel: string, locale: string, productId: number) => {
  await fetch(Routing.generate(ROUTE_NAME), {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      Accept: "application/json"
    },
    body: `title=${encodeURIComponent(title)}&channel=${encodeURIComponent(channel)}&locale=${encodeURIComponent(locale)}&productId=${encodeURIComponent(productId)}`
  });

  return [];
};

export default fetchProductModelIgnoreTitleSuggestion;
