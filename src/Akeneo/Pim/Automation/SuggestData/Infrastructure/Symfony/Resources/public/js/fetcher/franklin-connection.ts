import * as JQuery from 'jquery';

const Routing = require('routing');

export interface ConnectionStatus {
  isActive: boolean;
}

export function getConfiguration(): JQueryPromise<any> {
  const url = Routing.generate('akeneo_suggest_data_connection_get');

  return JQuery.get(url);
}

export function getConnectionStatus(): JQueryPromise<any> {
  const url = Routing.generate('akeneo_suggest_data_connection_status_get');

  return JQuery.get(url);
}
