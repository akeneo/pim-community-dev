import * as JQuery from 'jquery';

const Routing = require('routing');

export function getIdentifiersMapping(): JQueryPromise<any> {
  const url = Routing.generate(
    'akeneo_suggest_data_identifiers_mapping_get'
  );

  return JQuery.get(url);
}
