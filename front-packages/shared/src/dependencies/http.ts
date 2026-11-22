/**
 * Small fetch wrapper used by the application services: same-origin JSON GET, flagged as XHR like jQuery did,
 * rejected with the HTTP status when the response is not successful.
 */
class HttpError extends Error {
  readonly status: number;

  constructor(url: string, status: number) {
    super(`Request to "${url}" failed with status ${status}`);
    Object.setPrototypeOf(this, HttpError.prototype);
    this.status = status;
  }
}

const getJson = async <T = any>(url: string): Promise<T> => {
  const response = await fetch(url, {
    credentials: 'same-origin',
    headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
  });

  if (!response.ok) {
    throw new HttpError(url, response.status);
  }

  return response.json();
};

export {getJson, HttpError};
