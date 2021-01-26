export const getJSON = (url: string) => {
  return fetch(url);
};

export const postJSON = (url: string, data: {}) => {
  return fetch(url, {
    body: JSON.stringify(data),
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
  });
};

export const putJSON = async (url: string, data: {}) => {
  return fetch(url, {
    method: 'PUT',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
    },
  });
};

export const deleteJSON = async (url: any, data: {} = {}) => {
  const promise = fetch(url, {
    method: 'DELETE',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
    },
  });

  return promise;
};
