const handleResponse = async (response: Response) => {
  switch (response.status) {
    case 204:
      return;
    case 200:
    case 400:
      return await response.json();
    case 401:
      window.location.replace('/user/login');

      throw new Error(response.statusText);
    default:
      throw new Error(`Unexpected status code: ${response.status}`);
  }
};

export {handleResponse};
