const httpPost = (url: string, params?: any) => {
  return fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...params.header,
    },
    method: 'POST',
    body: JSON.stringify(params.body),
  });
};

const httpGet = (url: string) => {
  return fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    method: 'GET',
  });
};

const httpPut = (url: string, params?: any) => {
  return fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...params.header,
    },
    method: 'PUT',
    body: JSON.stringify(params.body),
  });
};

const httpDelete = (url: string) => {
  return fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    method: 'DELETE',
  });
};

export { httpPost, httpPut, httpGet, httpDelete };
