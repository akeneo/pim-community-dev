const baseFetcher = async (route: string) => {
  const response = await fetch(route);

  return await response.json();
};

export {baseFetcher};
