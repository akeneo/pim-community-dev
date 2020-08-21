const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_dashboard_widget_families';

const fetchWidgetFamilies = async (channel: string, locale: string, familyCodes: string[]) => {
  let routeParams = {
    channel: channel,
    locale: locale,
    families: familyCodes
  };
  const response = await fetch(Routing.generate(ROUTE_NAME, routeParams));

  return await response.json();
};

export default fetchWidgetFamilies;
