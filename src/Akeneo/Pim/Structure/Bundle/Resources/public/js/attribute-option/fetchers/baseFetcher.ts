const baseFetcher = async (route: string) => {
  const response = await fetch(route, {
    method: 'GET',
    headers: [
      ['Content-type', 'application/json'],
      ['X-Requested-With', 'XMLHttpRequest'],
    ],
  });

  return await response.json();
};

export default baseFetcher;
