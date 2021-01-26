export const getJSON = (url: string) => {
  return fetch(url).then(response => response.json());
};

export const postJSON = (url: string, data: {}) => {
  return fetch(url, {
    body: JSON.stringify(data),
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
  }).then(response => response.json());
};

export const putJSON = async (url: string, data: {}) => {
  return fetch(url, {
    method: 'PUT',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
    },
  }).then(response => response.json());
};

export const deleteJSON = async (url: any, data: {} = {}) => {
  return fetch(url, {
    method: 'DELETE',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
    },
  }).then(response => response.json());
};
