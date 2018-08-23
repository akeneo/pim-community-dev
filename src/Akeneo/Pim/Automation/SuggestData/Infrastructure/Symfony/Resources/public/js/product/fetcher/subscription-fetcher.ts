import * as JQuery from 'jquery';

const Routing = require('routing');

export function isProductSubscribed(productId: number): JQueryPromise<any> {
  const url = Routing.generate(
    'akeneo_suggest_data_franklin_subscription_status',
    {productId: productId}
  );

  return JQuery.get(url);
}
