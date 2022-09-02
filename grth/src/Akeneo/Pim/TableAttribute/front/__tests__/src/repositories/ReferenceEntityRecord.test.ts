import {ReferenceEntityRecordRepository} from '../../../src';
import {referenceEntityRecordMocks} from '../../../src/fetchers/__mocks__/RecordFetcher';
import {RecordFetcher} from '../../../src/fetchers/RecordFetcher';

jest.mock('../../../src/fetchers/RecordFetcher');

const router = {
  generate: (route: string, parameters?: Record<string, string>) =>
    route + (parameters ? '?' + new URLSearchParams(parameters).toString() : ''),
  redirect: jest.fn(),
  redirectToRoute: jest.fn(),
};

describe('ReferenceEntityRecord Repository', () => {
  beforeEach(() => {
    ReferenceEntityRecordRepository.clearCache();
  });

  describe('search', () => {
    const props = {
      channel: 'mobile',
      locale: 'en_US',
      codes: ['lannion00893335_2e73_41e3_ac34_763fb6a35107'],
    };

    it('should store promises into cache', () => {
      const search = jest.spyOn(RecordFetcher, 'search');

      const promise = ReferenceEntityRecordRepository.search(router, 'city', props);
      const promise2 = ReferenceEntityRecordRepository.search(router, 'city', props);

      expect(search).toBeCalledTimes(1);
      expect(promise).toEqual(promise2);
    });

    it('should fetch only once the same record', async () => {
      const search = jest.spyOn(RecordFetcher, 'search');
      const records = await ReferenceEntityRecordRepository.search(router, 'city', props);
      expect(records).toHaveLength(1);

      const records2 = await ReferenceEntityRecordRepository.search(router, 'city', {
        ...props,
        codes: ['vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3', 'lannion00893335_2e73_41e3_ac34_763fb6a35107'],
      });
      expect(records2).toHaveLength(2);

      const firstCallCodes = search.mock.calls[0][2]?.codes;
      const secondCallCodes = search.mock.calls[1][2]?.codes;
      expect(firstCallCodes).toEqual(['lannion00893335_2e73_41e3_ac34_763fb6a35107']);
      expect(secondCallCodes).toEqual(['vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3']);
    });

    it('should not refetch a record if it is already stored with a different promise', async () => {
      const search = jest.spyOn(RecordFetcher, 'search');
      const records = await ReferenceEntityRecordRepository.search(router, 'city', props);
      expect(records).toHaveLength(1);

      const response2 = await ReferenceEntityRecordRepository.search(router, 'city', {
        ...props,
        search: 'L',
      });
      expect(response2).toHaveLength(1);
      expect(search).toBeCalledTimes(1);
    });

    it('should remove not found records from cache', async () => {
      const propsWithMultipleCodes = {
        ...props,
        codes: ['lannion00893335_2e73_41e3_ac34_763fb6a35107', 'unknown_record'],
      };

      const records = await ReferenceEntityRecordRepository.search(router, 'city', propsWithMultipleCodes);
      expect(records).toHaveLength(1);
    });
  });

  describe('findByCode', () => {
    it('should save the value into cache and return it', async () => {
      const findByCode = jest.spyOn(RecordFetcher, 'findByCode');
      const promise = ReferenceEntityRecordRepository.findByCode(
        router,
        'city',
        'lannion00893335_2e73_41e3_ac34_763fb6a35107'
      );
      const records = await Promise.resolve(promise);
      expect(records).toEqual(referenceEntityRecordMocks[0]);

      const promise2 = ReferenceEntityRecordRepository.findByCode(
        router,
        'city',
        'lannion00893335_2e73_41e3_ac34_763fb6a35107'
      );
      const records2 = await Promise.resolve(promise2);
      expect(records2).toEqual(referenceEntityRecordMocks[0]);
      expect(findByCode).toBeCalledTimes(1);
    });
  });

  describe('getCachedByCode', () => {
    it('should return cached value', async () => {
      const promise = ReferenceEntityRecordRepository.findByCode(
        router,
        'city',
        'lannion00893335_2e73_41e3_ac34_763fb6a35107'
      );
      const records = await Promise.resolve(promise);
      expect(records).toEqual(referenceEntityRecordMocks[0]);

      const records2 = ReferenceEntityRecordRepository.getCachedByCode(
        'city',
        'lannion00893335_2e73_41e3_ac34_763fb6a35107'
      );
      expect(records2).toEqual(referenceEntityRecordMocks[0]);
    });
  });
});
