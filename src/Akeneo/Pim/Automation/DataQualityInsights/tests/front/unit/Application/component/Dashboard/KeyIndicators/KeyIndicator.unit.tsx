import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {KeyIndicator} from '../../../../../../../front/src/application/component/Dashboard';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {renderDashboardWithProvider} from '../../../../../utils/render/renderDashboardWithProvider';
import {CountsByProductType} from '@akeneo-pim-community/data-quality-insights/src/domain';

import {useTranslate} from '@akeneo-pim-community/shared';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (i18nKey: string) => {
    if (i18nKey === 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.entities_to_work_on') {
      // we need the marker to test the KeyIndicator component, the i18nkey would not be sufficient
      return 'some text before marker [A] some text after marker';
    }
    return i18nKey;
  },
}));

const countsSamples: {[kind: string]: CountsByProductType} = {
  'no data': {
    products: {
      totalGood: 0,
      totalToImprove: 0,
    },
    product_models: {
      totalGood: 0,
      totalToImprove: 0,
    },
  },
  'only products to improve': {
    products: {
      totalGood: 1,
      totalToImprove: 1,
    },
    product_models: {
      totalGood: 0,
      totalToImprove: 0,
    },
  },
} as const;

interface RenderDashBoardParams {
  keyIndicatorCode?: string;
  counts?: CountsByProductType;
}

const defaultRenderDashboardParams: RenderDashBoardParams = {
  keyIndicatorCode: 'has_image',
  counts: countsSamples['no data'],
};

const renderDashboard = ({
  keyIndicatorCode = 'has_image',
  counts = countsSamples['no data'],
}: RenderDashBoardParams = defaultRenderDashboardParams) =>
  renderDashboardWithProvider(
    <KeyIndicatorsProvider tips={keyIndicatorsTips}>
      <KeyIndicator type={keyIndicatorCode} title={'My key indicator'} counts={counts}>
        <span>an_icon</span>
      </KeyIndicator>
    </KeyIndicatorsProvider>
  );

describe('KeyIndicator', function () {
  //
  // Nothing to improve
  //
  describe('when there is no products nor product models  to improve', function () {
    test('must display the expected icon', function () {
      const {getByText} = renderDashboard();
      expect(getByText('an_icon')).toBeInTheDocument();
    });

    test('must display the expected title', function () {
      const {getByText} = renderDashboard();
      expect(getByText('My key indicator')).toBeInTheDocument();
    });

    test('must display a 0% progressbar', function () {
      const {getByRole} = renderDashboard();
      expect(getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');
    });

    test('must display a specific message indicating that there is no data to exploit', function () {
      const keyIndicatorCode = 'has_image';
      const {getByText} = renderDashboard({keyIndicatorCode});
      expect(
        getByText(`akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.${keyIndicatorCode}.no_data`)
      ).toBeInTheDocument();
    });
  });

  //
  // Just products to improve
  //
  describe.only('when there is only products to improve', function () {
    const counts = countsSamples['only products to improve'];

    test('must display the expected icon', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('an_icon')).toBeInTheDocument();
    });

    test('must display the expected title', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('My key indicator')).toBeInTheDocument();
    });

    test('must display a progressbar with correct percentage ', function () {
      const {getByRole} = renderDashboard({counts});
      expect(getByRole('progressbar')).toHaveAttribute('aria-valuenow', '50');
    });

    test('must display a message containing a button leading to filtered product grid', async function () {
      // check that the button is there, click on it, expected some function to be called with approriate filtering
      const {getByRole} = renderDashboard({counts});

      const button = getByRole('button');
      expect(button).toBeInTheDocument();
    });

    test('must display a message corresponding to the score', function () {
      const {getByText} = renderDashboard({counts});
      expect(
        getByText(
          'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.first_step.message2'
        )
      ).toBeInTheDocument();
    });
  });

  //
  // Just product models to improve
  //
  describe('when there is only product models to improve', function () {
    test('must display a progressbar with correct percentage', function () {
      // check only progress bar and value
    });
    test('must display a message containing a button leading to filtered product grid', function () {
      // check that the button is there, click on it, expected some function to be called with approriate filtering
    });
  });

  //
  // Both product and product models to improve
  //
  describe('when there is both products and product models to improve', function () {
    test('must display a progressbar with correct percentage', function () {
      // check only progress bar and value
    });
    test('must display a message containing a button leading to filtered product grid', function () {
      // check that 2 buttonq  are there, click on them, expected some function to be called with approriate filtering
    });
  });
});

test('It displays a key indicator with products to work on', () => {
  const {getByText} = renderDashboardWithProvider(
    <KeyIndicatorsProvider tips={keyIndicatorsTips}>
      <KeyIndicator
        type={'has_image'}
        title={'My key indicator'}
        counts={{
          products: {
            totalGood: 0,
            totalToImprove: 0,
          },
          product_models: {
            totalGood: 0,
            totalToImprove: 0,
          },
        }}
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
