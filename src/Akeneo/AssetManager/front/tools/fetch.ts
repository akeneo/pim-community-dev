import * as $ from 'jquery';
import promisify from 'akeneoassetmanager/tools/promisify';

export const getJSON = (...args: any[]) => {
  const promise = $.getJSON.apply($, args);

  return promisify(promise);
};

export const postJSON = (url: string, data: {}) => {
  const promise = $.ajax(url, {
    data: JSON.stringify(data),
    method: 'POST',
    contentType: 'application/json',
    dataType: 'json',
  });

  return promisify(promise);
};

export const putJSON = (url: string, data: {}) => {
  const promise = $.ajax(url, {
    data: JSON.stringify(data),
    method: 'PUT',
    contentType: 'application/json',
    dataType: 'json',
  });

  return promisify(promise);
};

export const deleteJSON = (url: any) => {
  const promise = $.ajax({url, type: 'DELETE'});

  return promisify(promise);
};
