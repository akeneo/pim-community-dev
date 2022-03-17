import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {KeyIndicatorAboutProducts} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {renderDashboardWithProvider} from '../../../../../utils/render/renderDashboardWithProvider';
import {CountsByProductType, KeyIndicatorProducts} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {FollowKeyIndicatorResultHandler} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions';

import {fireEvent} from '@testing-library/react';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...(jest.requireActual('@akeneo-pim-community/shared') as object),
  useTranslate: () => (i18nKey: string) => {
    switch (i18nKey) {
      case 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.entities_to_work_on':
        // we need the marker to test the KeyIndicator component, the i18nkey would not be sufficient
        return 'some text before marker <improvable_products_count_link/> some text after marker';
      case 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.entities_to_work_on_2_kinds':
        return 'some text <improvable_products_count_link/> and <improvable_product_models_count_link/> some other text';
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
  'nothing to improve': {
    products: {
      totalGood: 1,
      totalToImprove: 0,
    },
    product_models: {
      totalGood: 1,
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
  'only product models to improve': {
    products: {
      totalGood: 0,
      totalToImprove: 0,
    },
    product_models: {
      totalGood: 1,
      totalToImprove: 1,
    },
  },
  'both products and product models to improve': {
    products: {
      totalGood: 1,
      totalToImprove: 1,
    },
    product_models: {
      totalGood: 1,
      totalToImprove: 2,
    },
  },
} as const;

interface RenderDashBoardParams {
  keyIndicatorCode?: KeyIndicatorProducts;
  counts: CountsByProductType;
  followResults?: FollowKeyIndicatorResultHandler;
}

const renderDashboard = ({keyIndicatorCode = 'has_image', counts, followResults}: RenderDashBoardParams) =>
  renderDashboardWithProvider(
    <KeyIndicatorsProvider tips={keyIndicatorsTips}>
      <KeyIndicatorAboutProducts
        type={keyIndicatorCode}
        title={'My key indicator i18n key'}
        counts={counts}
        followResults={followResults}
      >
        <span>an_icon</span>
      </KeyIndicatorAboutProducts>
    </KeyIndicatorsProvider>
  );

describe('KeyIndicatorAboutProducts', function () {
  //
  // No data (no products nor product models at all)
  //
  describe('when there is no products nor product models at all', function () {
    const counts = countsSamples['no data'];

    test('must display the expected icon', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('an_icon')).toBeInTheDocument();
    });

    test('must display the expected title', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('My key indicator i18n key')).toBeInTheDocument();
    });

    test('must display a 0% progressbar', function () {
      const {getByRole} = renderDashboard({counts});
      expect(getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');
    });

    test('must display a specific message indicating that there is no data to exploit', function () {
      const keyIndicatorCode = 'has_image';
      const {getByText} = renderDashboard({keyIndicatorCode, counts});
      expect(
        getByText(`akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.${keyIndicatorCode}.no_data`)
      ).toBeInTheDocument();
    });
  });

  //
  // Nothing to improve
  //
  describe('when there is no products nor product models to improve', function () {
    const counts = countsSamples['nothing to improve'];

    test('must display the expected icon', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('an_icon')).toBeInTheDocument();
    });

    test('must display the expected title', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('My key indicator i18n key')).toBeInTheDocument();
    });

    test('must display a 100% progressbar', function () {
      const {getByRole} = renderDashboard({counts});
      expect(getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');
    });

    test('must display a message corresponding to the score', function () {
      const {getByText} = renderDashboard({counts});
      expect(
        getByText(
          /akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.perfect_score_step.message\d/
        )
      ).toBeInTheDocument();
    });
  });

  //
  // Just products to improve
  //
  describe('when there is only products to improve', function () {
    const counts = countsSamples['only products to improve'];

    test('must display the expected icon', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('an_icon')).toBeInTheDocument();
    });

    test('must display the expected title', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('My key indicator i18n key')).toBeInTheDocument();
    });

    test('must display a progressbar with correct percentage ', function () {
      const {getByRole} = renderDashboard({counts});
      expect(getByRole('progressbar')).toHaveAttribute('aria-valuenow', '50');
    });

    test('must display a message containing a button leading to filtered product grid', function () {
      const followResults = jest.fn();
      const {getByRole} = renderDashboard({counts, followResults});

      const button = getByRole('button');

      fireEvent.click(button);

      expect(followResults).toHaveBeenCalledWith('catalogScope', 'en_US', 'product', undefined, null, null, undefined);
    });

    test('must display a message corresponding to the score', function () {
      const {getByText} = renderDashboard({counts});
      expect(
        getByText(
          /akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.first_step.message\d/
        )
      ).toBeInTheDocument();
    });
  });

  //
  // Just product models to improve
  //
  describe('when there is only product models to improve', function () {
    const counts = countsSamples['only product models to improve'];

    test('must display the expected icon', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('an_icon')).toBeInTheDocument();
    });

    test('must display the expected title', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('My key indicator i18n key')).toBeInTheDocument();
    });

    test('must display a progressbar with correct percentage ', function () {
      const {getByRole} = renderDashboard({counts});
      // the progress bar display ratio for products, not product-models
      expect(getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');
    });

    test('must display a message containing a button leading to filtered product grid', function () {
      const followResults = jest.fn();
      const {getByRole} = renderDashboard({counts, followResults});

      const button = getByRole('button');

      fireEvent.click(button);

      expect(followResults).toHaveBeenCalledWith(
        'catalogScope',
        'en_US',
        'product_model',
        undefined,
        null,
        null,
        undefined
      );
    });

    test('must display a message corresponding to the score', function () {
      const {getByText} = renderDashboard({counts});
      expect(
        getByText(
          /akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.first_step.message\d/
        )
      ).toBeInTheDocument();
    });
  });

  //
  // Both product and product models to improve
  //
  describe('when there is both products and product models to improve', function () {
    const counts = countsSamples['both products and product models to improve'];

    test('must display the expected icon', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('an_icon')).toBeInTheDocument();
    });

    test('must display the expected title', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText('My key indicator i18n key')).toBeInTheDocument();
    });

    test('must display a progressbar with correct percentage ', function () {
      const {getByRole} = renderDashboard({counts});
      // the progress bar display ratio for products, not product-models
      expect(getByRole('progressbar')).toHaveAttribute('aria-valuenow', '50');
    });

    test('must display a message containing a button leading to filtered product grid', function () {
      const followResults = jest.fn();
      const {queryAllByRole} = renderDashboard({counts, followResults});

      const [buttonA, buttonB] = queryAllByRole('button');

      expect(buttonA !== undefined && buttonB !== undefined).toBe(true);

      fireEvent.click(buttonA);

      expect(followResults).toHaveBeenCalledWith('catalogScope', 'en_US', 'product', undefined, null, null, undefined);

      fireEvent.click(buttonB);

      expect(followResults).toHaveBeenCalledWith(
        'catalogScope',
        'en_US',
        'product_model',
        undefined,
        null,
        null,
        undefined
      );
    });

    test('must display a message corresponding to the score', function () {
      const {getByText} = renderDashboard({counts});
      expect(
        getByText(
          /akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.first_step.message\d/
        )
      ).toBeInTheDocument();
    });
  });
});
