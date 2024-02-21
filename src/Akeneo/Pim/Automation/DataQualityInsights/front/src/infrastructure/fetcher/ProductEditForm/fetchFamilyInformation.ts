const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_family_rest_get';

const fetchFamilyInformation = async (family: string) => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      identifier: family,
    })
  );

  return await response.json();
};

export default fetchFamilyInformation;
