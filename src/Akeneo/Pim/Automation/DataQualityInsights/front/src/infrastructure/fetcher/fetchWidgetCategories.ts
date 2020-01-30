const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_dashboard_widget_categories';

const fetchWidgetCategories = async (channel: string, locale: string, categoryCodes: string[]) => {
  let routeParams = {
    channel: channel,
    locale: locale,
    categories: categoryCodes
  };
  const response = await fetch(Routing.generate(ROUTE_NAME, routeParams));

  return await response.json();
};

export default fetchWidgetCategories;
