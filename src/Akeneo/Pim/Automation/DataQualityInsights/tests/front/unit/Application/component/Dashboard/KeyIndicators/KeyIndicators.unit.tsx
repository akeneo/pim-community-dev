import React from 'react';
import {KeyIndicator, KeyIndicators} from '../../../../../../../front/src/application/component/Dashboard';
import {useFetchKeyIndicators} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {renderDashboardWithProvider} from '../../../../../utils/render/renderDashboardWithProvider';
import '@testing-library/jest-dom/extend-expect';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks');

test('It displays 2 key indicators', async () => {
  useFetchKeyIndicators.mockReturnValueOnce({
    has_image: {
      ratioGood: 25.65,
      totalToImprove: 5000,
    },
    good_enrichment: {
      ratioGood: 25.65,
      totalToImprove: 5000,
    },
  });

  const {getByText, queryByTestId} = renderComponent();

  expect(
    getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')
  ).toBeInTheDocument();
  expect(
    getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title')
  ).toBeInTheDocument();
  expect(queryByTestId('dqi-key-indicator-loading')).toBeNull();
});

test('It displays a loading when key indicators have not been loaded yet', async () => {
  useFetchKeyIndicators.mockReturnValueOnce(null);

  const {queryByText, queryByTestId} = renderComponent();

  expect(queryByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')).toBeNull();
  expect(
    queryByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title')
  ).toBeNull();
  expect(queryByTestId('dqi-key-indicator-loading')).toBeInTheDocument();
});

function renderComponent() {
  return renderDashboardWithProvider(
    <KeyIndicatorsProvider tips={keyIndicatorsTips}>
      <KeyIndicators channel={'ecommerce'} locale={'en_US'} category={null} family={null}>
        <KeyIndicator
          type={'has_image'}
          title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title'}
        />
        <KeyIndicator
          type={'good_enrichment'}
          title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title'}
          resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
        />
      </KeyIndicators>
    </KeyIndicatorsProvider>
  );
}
