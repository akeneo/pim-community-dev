import * as JQuery from 'jquery';

const Routing = require('routing');

export function getConfiguration(): JQueryPromise<any> {
  const url = Routing.generate('akeneo_suggest_data_connection_get');

  return JQuery.get(url);
}

export function isConnectionActivated(): JQueryPromise<any> {
  const url = Routing.generate('akeneo_suggest_data_is_connection_activated');

  return JQuery.get(url);
}
