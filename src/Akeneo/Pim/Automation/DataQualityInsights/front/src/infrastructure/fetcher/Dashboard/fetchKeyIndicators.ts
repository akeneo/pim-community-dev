const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_dashboard_key_indicators';

const fetchKeyIndicators = async (
  channel: string,
  locale: string,
  familyCode: string | null,
  categoryCode: string | null
) => {
  let routeParams = {
    channel: channel,
    locale: locale,
    family: familyCode,
    category: categoryCode,
  };

  const response = await fetch(Routing.generate(ROUTE_NAME, routeParams));

  return await response.json();
};

export {fetchKeyIndicators};
