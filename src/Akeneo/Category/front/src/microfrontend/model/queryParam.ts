const createQueryParam = (parameters?: object) => {
  if (!parameters) return '';

  const queryParameters = Object.entries(parameters).map(([key, val]) => {
    if (Array.isArray(val)) {
      return val.map(value => `${key}[]=${value}`).join('&');
    }

    return `${key}=${val}`;
  });

  return queryParameters.length > 0 ? '?' + queryParameters.join('&') : '';
};

export {createQueryParam};
