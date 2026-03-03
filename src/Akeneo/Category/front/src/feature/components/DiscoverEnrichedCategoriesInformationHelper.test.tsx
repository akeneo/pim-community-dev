import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DiscoverEnrichedCategoriesInformationHelper} from './DiscoverEnrichedCategoriesInformationHelper';

jest.mock('@akeneo-pim-community/shared', () => {
  const originalModule = jest.requireActual('@akeneo-pim-community/shared');
  return {
    __esModule: true,
    ...originalModule,
  };
});

describe('DiscoverEnrichedCategoriesInformationHelper', () => {
  test('it renders the component', () => {
    const {queryByTestId} = renderWithProviders(<DiscoverEnrichedCategoriesInformationHelper />);

    expect(queryByTestId('discover-enriched-categories-information-helper')).toBeInTheDocument();
  });
});
