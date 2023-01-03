import {renderWithProviders, useFeatureFlags} from '@akeneo-pim-community/shared';
import {DiscoverEnrichedCategoriesInformationHelper} from './DiscoverEnrichedCategoriesInformationHelper';

jest.mock('@akeneo-pim-community/shared', () => {
  const originalModule = jest.requireActual('@akeneo-pim-community/shared');
  return {
    __esModule: true,
    ...originalModule,
    useFeatureFlags: jest.fn(),
  };
});

beforeEach(() => {
  (useFeatureFlags as jest.Mock).mockImplementation(() => ({
    isEnabled: (feature: string) => true,
  }));
});

describe('DiscoverEnrichedCategoriesInformationHelper', () => {
  test('it renders the component when the feature flag enriched_category is enabled', () => {
    const {queryByTestId} = renderWithProviders(<DiscoverEnrichedCategoriesInformationHelper />);

    expect(queryByTestId('discover-enriched-categories-information-helper')).toBeInTheDocument();
  });
  test('it does not render the component when the feature flag enriched_category is disabled', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
      isEnabled: (feature: string) =>
        ({
          enriched_category: false,
        }[feature] ?? true),
    }));
    const {queryByTestId} = renderWithProviders(<DiscoverEnrichedCategoriesInformationHelper />);

    expect(queryByTestId('discover-enriched-categories-information-helper')).not.toBeInTheDocument();
  });
});
