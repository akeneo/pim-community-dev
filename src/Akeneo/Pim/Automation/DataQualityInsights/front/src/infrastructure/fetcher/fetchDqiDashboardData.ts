const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_dashboard';

const fetchDqiDashboardData = async (channel: string, locale: string) => {
  const response = await fetch(Routing.generate(ROUTE_NAME, {
    channel: channel,
    locale: locale
  }));
  const json =  await response.json();
  return json;
};

export default fetchDqiDashboardData;
