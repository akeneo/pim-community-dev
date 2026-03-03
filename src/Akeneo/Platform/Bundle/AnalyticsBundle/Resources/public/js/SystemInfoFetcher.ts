const Routing = require('routing');

const ROUTE_NAME = 'pim_analytics_system_info_rest_index';

const fetchSystemInfo = async () => {
  const response = await fetch(Routing.generate(ROUTE_NAME));

  return await response.json();
};

export {fetchSystemInfo};
