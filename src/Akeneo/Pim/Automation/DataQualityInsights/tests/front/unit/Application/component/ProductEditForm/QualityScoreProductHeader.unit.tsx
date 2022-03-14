import {screen} from '@testing-library/react';
import React from 'react';
import {QualityScoreProductHeader} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/QualityScoreProductHeader';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {useCatalogContext, useFetchQualityScore} from '../../../../../../front/src/infrastructure/hooks';

beforeEach(() => {
  jest.clearAllMocks();
  jest.resetAllMocks();
});

afterAll(() => {
  jest.resetAllMocks();
});

jest.mock('../../../../../../front/src/infrastructure/hooks', () => ({
  useCatalogContext: jest.fn(),
  useFetchQualityScore: jest.fn(),
}));

describe('QualityScoreProductHeader', () => {
  test('it renders a skeleton as loader component when scores are loading', () => {
    const catalogContextMock = {channel: 'mobile', locale: 'fr_FR'};
    const fetchQualityScoreMock = {score: null, productType: 'product', isLoading: true};

    (useCatalogContext as jest.Mock).mockReturnValue(catalogContextMock);
    (useFetchQualityScore as jest.Mock).mockReturnValue(fetchQualityScoreMock);

    renderWithProviders(<QualityScoreProductHeader />);

    expect(screen.getByTestId('quality-score-loader')).toBeInTheDocument();
  });

  test.each`
    score    | product_type
    ${'N/A'} | ${'product'}
    ${'N/A'} | ${'product_model'}
    ${null}  | ${'product'}
    ${null}  | ${'product_model'}
  `('it renders a badge labelled "pending" when score of a $product_type is $score', ({score, product_type}) => {
    const catalogContextMock = {channel: 'mobile', locale: 'fr_FR'};
    const fetchQualityScoreMock = {score, productType: product_type, isLoading: false};

    (useCatalogContext as jest.Mock).mockReturnValue(catalogContextMock);
    (useFetchQualityScore as jest.Mock).mockReturnValue(fetchQualityScoreMock);

    renderWithProviders(<QualityScoreProductHeader />);

    expect(screen.getByTestId('quality-score-pending')).toBeInTheDocument();
    expect(screen.getByText('akeneo_data_quality_insights.quality_score.pending')).toBeInTheDocument();
  });

  test('it renders a score bar when scores are loaded and score is in [A-E]', () => {
    const catalogContextMock = {channel: 'mobile', locale: 'fr_FR'};
    const fetchQualityScoreMock = {score: 'A', productType: 'product', isLoading: false};

    (useCatalogContext as jest.Mock).mockReturnValue(catalogContextMock);
    (useFetchQualityScore as jest.Mock).mockReturnValue(fetchQualityScoreMock);

    renderWithProviders(<QualityScoreProductHeader />);

    expect(screen.getByTestId('quality-score-bar')).toBeInTheDocument();
  });
});
