import React from 'react';
import {KeyIndicators} from '../../../../../../../front/src/application/component/Dashboard';
import {useFetchKeyIndicators} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {renderDashboardWithProvider} from '../../../../../utils/render/renderDashboardWithProvider';
import '@testing-library/jest-dom/extend-expect';
import {keyIndicatorDescriptorsCE} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/keyIndicatorDescriptorsCE';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks');

test('It displays 2 key indicators', async () => {
  (useFetchKeyIndicators as jest.Mock).mockReturnValueOnce({
    has_image: {
      products: {
        totalGood: 25,
        totalToImprove: 5000,
      },
      product_models: {
        totalGood: 30,
        totalToImprove: 3000,
      },
    },
    good_enrichment: {
      products: {
        totalGood: 25,
        totalToImprove: 5000,
      },
      product_models: {
        totalGood: 30,
        totalToImprove: 3000,
      },
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
  (useFetchKeyIndicators as jest.Mock).mockReturnValueOnce(null);

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
      <KeyIndicators
        channel={'ecommerce'}
        locale={'en_US'}
        category={null}
        family={null}
        keyIndicatorDescriptors={keyIndicatorDescriptorsCE}
      />
    </KeyIndicatorsProvider>
  );
}
