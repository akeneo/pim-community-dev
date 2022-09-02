class BackendError extends Error {
  constructor(public response: any) {
    super(response.statusText);
  }
}

const handleResponse = async (response: Response) => {
  switch (response.status) {
    case 204:
      return;
    case 200:
    case 400:
      return await response.json();
    case 401:
      window.location.replace('/user/login');

      throw new BackendError(response);
    default:
      throw new BackendError(response);
  }
};

export {handleResponse};
