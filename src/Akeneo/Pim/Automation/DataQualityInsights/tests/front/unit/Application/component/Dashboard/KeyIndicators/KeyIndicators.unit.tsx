import React from 'react';
import {renderWithAppContextHelper} from '../../../../../utils/render';
import {
  KeyIndicators,
  ProductsWithAnImage,
  ProductsWithGoodEnrichment
} from '../../../../../../../front/src/application/component/Dashboard';
import {useFetchKeyIndicators} from "@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks";

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks');

test('It displays 2 key indicators', async() => {
  useFetchKeyIndicators.mockReturnValueOnce({
    'has_image': {
      'ratio': 25.65,
      'total': 5000
    },
    'good_enrichment': {
      'ratio': 25.65,
      'total': 5000
    }
  });

  const {getByText, queryByTestId} = renderComponent();

  expect(getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')).toBeInTheDocument();
  expect(getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title')).toBeInTheDocument();
  expect(queryByTestId('dqi-key-indicator-loading')).toBeNull();
});

test('It displays a loading when key indicators have not been loaded yet', async() => {
  useFetchKeyIndicators.mockReturnValueOnce(null);

  const {queryByText, queryByTestId} = renderComponent();

  expect(queryByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')).toBeNull();
  expect(queryByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title')).toBeNull();
  expect(queryByTestId('dqi-key-indicator-loading')).toBeInTheDocument();
});

test('It displays only the key indicators for which we have data', async() => {
  useFetchKeyIndicators.mockReturnValueOnce({
    'good_enrichment': {
      'ratio': 25.65,
      'total': 5000
    }
  });

  const {getByText, queryByText, queryByTestId} = renderComponent();

  expect(queryByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')).toBeNull();
  expect(getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title')).toBeInTheDocument();
  expect(queryByTestId('dqi-key-indicator-loading')).toBeNull();
});

function renderComponent()
{
  return renderWithAppContextHelper(
    <KeyIndicators channel={'ecommerce'} locale={'en_US'} category={null} family={null}>
      <ProductsWithAnImage type="has_image"/>
      <ProductsWithGoodEnrichment type="good_enrichment"/>
    </KeyIndicators>
  )
}
