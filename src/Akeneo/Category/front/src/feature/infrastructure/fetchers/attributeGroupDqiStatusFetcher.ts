const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_get_all_attribute_groups_activation';

const fetchAllAttributeGroupsDqiStatus = async () => {
  const response = await fetch(Routing.generate(ROUTE_NAME));

  return await response.json();
};

export {fetchAllAttributeGroupsDqiStatus};
