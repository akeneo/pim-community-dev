import * as $ from 'jquery';
import promisify from 'akeneoenrichedentity/tools/promisify';

export const getJSON = function({}: any) {
  const promise = $.getJSON.apply($, arguments);

  return promisify(promise);
};

export const postJSON = function(url: any, data: {}) {
  const promise = $.post(url, JSON.stringify(data), null, 'json');

  return promisify(promise);
};
