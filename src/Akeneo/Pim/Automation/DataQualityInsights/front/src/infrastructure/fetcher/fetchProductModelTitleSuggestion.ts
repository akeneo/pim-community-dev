import {Product} from "../../domain";

const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_product_model_check_title_suggestion';

const fetchProductModelTitleSuggestion = async (product: Product, channel: string, locale: string) => {
  const response = await fetch(Routing.generate(ROUTE_NAME), {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      Accept: "application/json"
    },
    // @ts-ignore
    body: `productId=${encodeURIComponent(product.meta.id)}&channel=${encodeURIComponent(channel)}&locale=${encodeURIComponent(locale)}`
  });

  const data = await response.json();

  if (data === {}) {
    return [];
  }

  return data;
};

export default fetchProductModelTitleSuggestion;
