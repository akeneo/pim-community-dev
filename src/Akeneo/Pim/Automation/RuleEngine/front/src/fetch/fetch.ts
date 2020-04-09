const httpPost = (url: string, params?: any) => {
  return fetch(url, {
    headers: {
      "Content-Type": "application/json",
      "X-Requested-With": "XMLHttpRequest",
      ...params.header
    },
    credentials: "include",
    method: "POST",
    body: JSON.stringify(params.body)
  });
};

export { httpPost };
