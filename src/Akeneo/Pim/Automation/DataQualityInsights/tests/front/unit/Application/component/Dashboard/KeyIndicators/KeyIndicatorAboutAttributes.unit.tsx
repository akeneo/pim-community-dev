import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {KeyIndicatorAboutAttributes} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {renderDashboardWithProvider} from '../../../../../utils/render/renderDashboardWithProvider';
import {
  Counts,
  KeyIndicatorAttributes,
  KeyIndicatorsTips,
} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {AttributesKeyIndicatorLinkCallback} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions';

import {fireEvent} from '@testing-library/react';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...(jest.requireActual('@akeneo-pim-community/shared') as object),
  useTranslate: () => (i18nKey: string) => {
    switch (i18nKey) {
      case 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.attributes_to_work_on':
        // we need the marker to test the KeyIndicator component, the i18nkey would not be sufficient
        return 'some text before marker <improvable_attributes_count_link/> some text after marker';
    }
    return i18nKey;
  },
}));

const countsSamples: {[kind: string]: Counts} = {
  'no data': {
    totalGood: 0,
    totalToImprove: 0,
  },
  'no attribute to improve': {
    totalGood: 1,
    totalToImprove: 0,
  },
  'some attribute to improve': {
    totalGood: 1,
    totalToImprove: 1,
  },
} as const;

interface RenderDashBoardParams {
  keyIndicatorCode?: KeyIndicatorAttributes;
  counts: Counts;
  followResults?: AttributesKeyIndicatorLinkCallback;
}

const keyIndicatorsTips: KeyIndicatorsTips = {
  attributes_perfect_spelling: {
    first_step: [
      {
        message: 'first_step message #1',
      },
      {
        message: 'first_step message #2',
      },
    ],
    second_step: [
      {
        message: 'second_step message #1',
      },
      {
        message: 'second_step message #2',
      },
    ],
    third_step: [
      {
        message: 'third_step message #1',
      },
      {
        message: 'third_step message #2',
      },
    ],
    perfect_score_step: [
      {
        message: 'perfect_score_step message #1',
      },
      {
        message: 'perfect_score_step message #2',
      },
    ],
  },
};

const renderDashboard = ({
  keyIndicatorCode = 'attributes_perfect_spelling',
  counts,
  followResults,
}: RenderDashBoardParams) =>
  renderDashboardWithProvider(
    <KeyIndicatorsProvider tips={keyIndicatorsTips}>
      <KeyIndicatorAboutAttributes
        type={keyIndicatorCode}
        title={'My key indicator i18n key'}
        counts={counts}
        followResults={followResults}
      >
        <span>an_icon</span>
      </KeyIndicatorAboutAttributes>
    </KeyIndicatorsProvider>
  );

describe('KeyIndicatorAboutAttributes', function () {
  //
  // No data (no attribute to approve, no ok attribute)
  //
  describe('when there is no attributes at all', function () {
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
      const keyIndicatorCode = 'attributes_perfect_spelling';
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
    const counts = countsSamples['no attribute to improve'];

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
      expect(getByText(/perfect_score_step message #\d/)).toBeInTheDocument();
    });
  });

  //
  // Some attributes to improve
  //
  describe('when there is attributes to improve', function () {
    const counts = countsSamples['some attribute to improve'];

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

      expect(followResults).toHaveBeenCalledWith('en_US', undefined, null, undefined);
    });

    test('must display a message corresponding to the score', function () {
      const {getByText} = renderDashboard({counts});
      expect(getByText(/first_step message #\d/)).toBeInTheDocument();
    });
  });
});
