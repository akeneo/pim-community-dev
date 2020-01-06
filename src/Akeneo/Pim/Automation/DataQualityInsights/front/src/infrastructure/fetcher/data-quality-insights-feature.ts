const Routing = require('routing');

export interface DataQualityInsightsFeature {
  isActive: boolean;
}

export async function getDataQualityInsightsFeature(): Promise<DataQualityInsightsFeature> {
  const url = Routing.generate('akeneo_data_quality_insights_feature_flag');

  const result = await fetch(url);

  return result.json();
}
