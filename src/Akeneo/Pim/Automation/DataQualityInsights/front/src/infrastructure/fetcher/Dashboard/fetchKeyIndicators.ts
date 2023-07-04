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

  const url = Routing.generate(ROUTE_NAME, routeParams);
  try {
    const response = await fetch(url);

    return await response.json();
  } catch (error) {
    return null;
  }
};

export {fetchKeyIndicators};
