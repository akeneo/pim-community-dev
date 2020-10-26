const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_get_attribute_group_activation';

const fetchAttributeGroupStatus = async (groupCode: string) => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      attributeGroupCode: groupCode,
    })
  );

  return await response.json();
};

export default fetchAttributeGroupStatus;
