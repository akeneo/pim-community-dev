import * as $ from 'jquery';
import promisify from 'akeneoenrichedentity/tools/promisify';

export const getJSON = function({}: any) {
  const promise = $.getJSON.apply($, arguments);

  return promisify(promise);
};
