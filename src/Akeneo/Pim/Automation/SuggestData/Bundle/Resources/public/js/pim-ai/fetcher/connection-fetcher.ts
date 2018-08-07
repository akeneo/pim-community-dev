import * as JQuery from 'jquery';

const Routing = require('routing');

export function getConfiguration(code: string): JQueryPromise<any> {
  const url = Routing.generate('akeneo_suggest_data_connection_get', {code: code});

  return JQuery.get(url);
}

export function isConnectionActivated(code: string): JQueryPromise<any> {
  const url = Routing.generate('akeneo_suggest_data_is_connection_activated', {code: code});

  return JQuery.get(url);
}
