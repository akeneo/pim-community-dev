import {screen} from '@testing-library/react';
import React from 'react';
import {useSelector} from 'react-redux';
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

jest.mock('react-redux', () => ({
  useSelector: jest.fn(),
}));

describe('QualityScoreProductHeader', () => {
  test('it renders a skeleton as loader component when scores are loading', () => {
    const reduxStateExtractMock = {id: 1, type: 'product', isProductEvaluating: false};
    const catalogContextMock = {channel: 'mobile', locale: 'fr_FR'};
    const fetchQualityScoreMock = {outcome: {status: 'loading'}, fetcher: () => Promise.resolve()};

    (useCatalogContext as jest.Mock).mockReturnValue(catalogContextMock);
    (useFetchQualityScore as jest.Mock).mockReturnValue(fetchQualityScoreMock);
    (useSelector as jest.Mock).mockReturnValue(reduxStateExtractMock);

    renderWithProviders(<QualityScoreProductHeader />);

    expect(screen.getByTestId('quality-score-pending')).toBeInTheDocument();
  });

  test.each`
    score    | productType
    ${'N/A'} | ${'product'}
    ${'N/A'} | ${'product_model'}
    ${null}  | ${'product'}
    ${null}  | ${'product_model'}
  `('it renders a badge labelled "pending" when score of a $product_type is $score', ({score, productType}) => {
    const reduxStateExtractMock = {id: 1, type: productType, isProductEvaluating: false};
    const catalogContextMock = {channel: 'mobile', locale: 'fr_FR'};
    const fetchQualityScoreMock = {
      outcome: {
        status: 'loaded',
        scores: {
          mobile: {fr_FR: score},
        },
      },
      fetcher: () => Promise.resolve(),
    };

    (useCatalogContext as jest.Mock).mockReturnValue(catalogContextMock);
    (useFetchQualityScore as jest.Mock).mockReturnValue(fetchQualityScoreMock);
    (useSelector as jest.Mock).mockReturnValue(reduxStateExtractMock);

    renderWithProviders(<QualityScoreProductHeader />);

    expect(screen.getByTestId('quality-score-pending')).toBeInTheDocument();
    expect(screen.getByText('akeneo_data_quality_insights.quality_score.pending')).toBeInTheDocument();
  });

  test('it renders a score bar when scores are loaded and score is in [A-E]', () => {
    const reduxStateExtractMock = {id: 1, type: 'product', isProductEvaluating: false};
    const catalogContextMock = {channel: 'mobile', locale: 'fr_FR'};
    const fetchQualityScoreMock = {
      outcome: {
        status: 'loaded',
        scores: {
          mobile: {fr_FR: 'A'},
        },
      },
      fetcher: () => Promise.resolve(),
    };

    (useCatalogContext as jest.Mock).mockReturnValue(catalogContextMock);
    (useFetchQualityScore as jest.Mock).mockReturnValue(fetchQualityScoreMock);
    (useSelector as jest.Mock).mockReturnValue(reduxStateExtractMock);

    renderWithProviders(<QualityScoreProductHeader />);

    expect(screen.getByTestId('quality-score-bar')).toBeInTheDocument();
  });
});
