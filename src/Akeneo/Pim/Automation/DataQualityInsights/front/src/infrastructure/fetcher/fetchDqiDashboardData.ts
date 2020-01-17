const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_dashboard';

const fetchDqiDashboardData = async (channel: string, locale: string, periodicity: string, familyCode: string | null) => {

  let routeParams = {
    channel: channel,
    locale: locale,
    periodicity: periodicity,
    family: familyCode
  };

  const response = await fetch(Routing.generate(ROUTE_NAME, routeParams));

  return await response.json();
};

export default fetchDqiDashboardData;
