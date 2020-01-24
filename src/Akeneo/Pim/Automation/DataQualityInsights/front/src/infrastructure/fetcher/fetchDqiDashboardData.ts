const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_dashboard_overview';

const fetchDqiDashboardData = async (channel: string, locale: string, timePeriod: string, familyCode: string | null, categoryCode: string | null) => {

  let routeParams = {
    channel: channel,
    locale: locale,
    timePeriod: timePeriod,
    family: familyCode,
    category: categoryCode
  };

  const response = await fetch(Routing.generate(ROUTE_NAME, routeParams));

  return await response.json();
};

export default fetchDqiDashboardData;
