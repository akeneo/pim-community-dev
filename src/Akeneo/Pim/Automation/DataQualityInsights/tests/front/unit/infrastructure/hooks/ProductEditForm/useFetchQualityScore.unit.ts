import {renderHook, act} from '@testing-library/react-hooks';
import {useFetchQualityScore} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks';

import {QualityScoreModel} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {
  RetryDelayCalculator,
  sleep,
} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/ProductEditForm/useFetchQualityScore';

const someScores: QualityScoreModel = {
  channel_web: {
    en_US: 'A',
    fr_FR: 'E',
  },
  channel_mobile: {
    en_US: 'B',
    fr_FR: 'D',
  },
};

const getRetryDelay: RetryDelayCalculator = _ => 10; // we don't care here, we just need to have fast tests

let fetchQualityScoreMock = jest.fn();

jest.mock(
  '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/ProductEditForm/fetchQualityScore',
  () => ({
    fetchQualityScore: () => fetchQualityScoreMock(),
  })
);

const renderUseFetchQualityScore = () =>
  renderHook((props: Parameters<typeof useFetchQualityScore>) => useFetchQualityScore(...props), {
    initialProps: ['product', 1, getRetryDelay],
  });

describe('useFetchQualityScore', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('must starts with status "init" and return a function for reloading scores', async () => {
    const {
      result: {error, current},
    } = renderUseFetchQualityScore();

    expect(error).toBeUndefined();
    expect(current).not.toBeUndefined();
    expect(new Set(Object.keys(current))).toEqual(new Set(['fetcher', 'outcome']));

    const {outcome, fetcher} = current;

    expect(outcome.status).toEqual('init');
    expect(fetcher).toBeInstanceOf(Function);
  });

  describe('when backend returns 200 OK', () => {
    describe('when the n-th call to backend brings scores (n<10)', () => {
      test.each([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])(
        'must give back scores when fetcher is called and only the call #%i to backend brings scores',
        async (n: number) => {
          const {result, waitFor} = renderUseFetchQualityScore();

          for (let i = 1; i < n; i++) {
            fetchQualityScoreMock.mockImplementationOnce(async () => {
              await sleep(10);
              return {evaluations_available: false};
            });
          }
          fetchQualityScoreMock.mockImplementationOnce(async () => {
            await sleep(10);
            return {evaluations_available: true, scores: someScores};
          });

          await act(() => result.current.fetcher());

          await waitFor(() => {
            expect(result.current.outcome.status).toEqual('loaded');
          });

          // @ts-ignore
          expect(result.current.outcome.scores).toEqual(someScores);
        }
      );
    });

    describe('when the none of the 10 calls to backend bring scores', () => {
      test('must end up in "attempts exhausted" state', async () => {
        const {result, waitFor} = renderUseFetchQualityScore();

        for (let i = 1; i < 11; i++) {
          fetchQualityScoreMock.mockImplementationOnce(async () => {
            await sleep(10);
            return {evaluations_available: false};
          });
        }

        await act(() => result.current.fetcher());

        await waitFor(() => {
          expect(result.current.outcome.status).toEqual('attempts exhausted');
        });
      });
    });
  });

  describe('when backend request fails', () => {
    describe('when the n-th call to backend fails (n<10)', () => {
      test.each([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])(
        'must end-up with "failed" status after call #%i',
        async (n: number) => {
          const {result, waitFor} = renderUseFetchQualityScore();

          for (let i = 1; i < n; i++) {
            fetchQualityScoreMock.mockImplementationOnce(async () => {
              await sleep(10);
              return {evaluations_available: false};
            });
          }
          fetchQualityScoreMock.mockImplementationOnce(async () => {
            await sleep(10);
            throw Error('some error message');
          });

          await act(() => result.current.fetcher());

          await waitFor(() => {
            expect(result.current.outcome.status).toEqual('failed');
          });

          // @ts-ignore
          expect(result.current.outcome.error).toEqual('some error message');
        }
      );
    });
  });
});
