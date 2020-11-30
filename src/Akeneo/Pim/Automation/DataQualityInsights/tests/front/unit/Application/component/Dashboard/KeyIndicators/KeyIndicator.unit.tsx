import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {KeyIndicator} from '../../../../../../../front/src/application/component/Dashboard';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {renderDashboardWithProvider} from '../../../../../utils/render/renderDashboardWithProvider';

test('It displays a key indicator with products to work on', () => {
  const {getByText} = renderDashboardWithProvider(
    <KeyIndicatorsProvider tips={keyIndicatorsTips}>
      <KeyIndicator
        type={'has_image'}
        title={'My key indicator'}
        totalToImprove={156412}
        ratioGood={22}
        resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
      >
        <span>an_icon</span>
      </KeyIndicator>
    </KeyIndicatorsProvider>
  );

  expect(getByText('an_icon')).toBeInTheDocument();
  expect(getByText('My key indicator')).toBeInTheDocument();
  expect(getByText('22%')).toBeInTheDocument();
  expect(
    getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on')
  ).toBeInTheDocument();
  expect(getByText(/has_image.messages.first_step.message/)).toBeInTheDocument();
});

test('It displays a key indicator with no product to work on', () => {
  const {getByText, queryByText} = renderDashboardWithProvider(
    <KeyIndicatorsProvider tips={keyIndicatorsTips}>
      <KeyIndicator
        type={'good_enrichment'}
        title={'My key indicator'}
        totalToImprove={0}
        ratioGood={100}
        resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
      />
    </KeyIndicatorsProvider>
  );

  expect(getByText('My key indicator')).toBeInTheDocument();
  expect(getByText('100%')).toBeInTheDocument();
  expect(queryByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on')).toBeNull();
  expect(getByText(/good_enrichment.messages.perfect_score_step/)).toBeInTheDocument();
});
