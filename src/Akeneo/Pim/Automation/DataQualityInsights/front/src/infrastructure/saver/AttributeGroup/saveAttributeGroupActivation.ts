const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_update_attribute_group_activation';

const saveAttributeGroupActivation = async (groupCode: string, isActivated: boolean) => {
  await fetch(Routing.generate(ROUTE_NAME), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: `attribute_group_code=${encodeURIComponent(groupCode)}&activated=${encodeURIComponent(isActivated)}`,
  });
};

export default saveAttributeGroupActivation;
