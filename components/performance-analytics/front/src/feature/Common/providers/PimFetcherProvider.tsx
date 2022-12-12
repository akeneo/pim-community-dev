import React, {FC, useMemo, useCallback} from 'react';
import {FetcherContext} from '../contexts';
import {TimeToEnrich} from '../../TimeToEnrich';
import {useRouter} from '@akeneo-pim-community/shared';
import {Channel, ChannelCode, Family, FamilyCode, Locale, LocaleCode} from '../models';

const FETCH_FAMILY_SIZE = 100;

const PimFetcherProvider: FC = ({children}) => {
  const router = useRouter();

  const fetchFamilies = useCallback(
    async (limit: number, page: number, search?: string): Promise<{[key: string]: Family}> => {
      const params = {
        options: {
          limit: limit,
          page: page,
          expanded: 0,
        },
      };

      if (search !== undefined && search.trim().length > 0) {
        params['search'] = search;
      }

      const response = await fetch(router.generate('pim_enrich_family_rest_index', params), {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'GET',
      });

      return await response.json();
    },
    [router]
  );

  const fetchAllFamilyLabelsRecursively = useCallback(
    (resolve: (families: {[key: string]: Family}) => void, page = 1, results: {[key: string]: Family} = {}) => {
      fetchFamilies(FETCH_FAMILY_SIZE, page).then((newFamilies: {[key: string]: Family}) => {
        const newResults = {...results, ...newFamilies};
        if (FETCH_FAMILY_SIZE > Object.keys(newFamilies).length) {
          resolve(newResults);
        } else {
          fetchAllFamilyLabelsRecursively(resolve, page + 1, newResults);
        }
      });
    },
    [fetchFamilies]
  );

  const fetcherValue = useMemo(
    () => ({
      timeToEnrich: {
        fetchHistoricalTimeToEnrich: async (
          startDate: string,
          endDate: string,
          periodType: string,
          filters: {
            families: FamilyCode[];
            channels: ChannelCode[];
            locales: LocaleCode[];
          }
        ): Promise<TimeToEnrich[]> => {
          const response = await fetch(
            router.generate('pimee_performance_analytics_historical_average_tte') +
              '?' +
              new URLSearchParams({
                start_date: startDate,
                end_date: endDate,
                period_type: periodType,
                families: filters?.families?.join(','),
                channels: filters?.channels?.join(','),
                locales: filters?.locales?.join(','),
              }),
            {
              method: 'GET',
              headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
            }
          );
          return await response.json();
        },
        fetchAverageTimeToEnrichByEntity: async (
          startDate: string,
          endDate: string,
          aggregationType: string,
          filters: {
            channels: ChannelCode[];
            locales: LocaleCode[];
          }
        ): Promise<TimeToEnrich[]> => {
          // @TODO JEL-112 the real implementation (need backend)
          return new Promise(resolve =>
            resolve([
              {
                code: 'accessories',
                value: 44,
              },
              {
                code: 'camcorders',
                value: 10,
              },
              {
                code: 'clothing',
                value: 41,
              },
              {
                code: 'digital_cameras',
                value: 100,
              },
            ])
          );
        },
      },
      family: {
        fetchFamilies,
        fetchAllFamilies: async (): Promise<{[key: string]: Family}> => {
          return new Promise(resolve => fetchAllFamilyLabelsRecursively(resolve));
        },
      },
      channel: {
        fetchChannels: async (): Promise<Channel[]> => {
          const url = router.generate('pim_enrich_channel_rest_index');

          const response = await fetch(url, {
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
            method: 'GET',
          });

          return await response.json();
        },
      },
      locale: {
        fetchActivatedLocales: async (): Promise<Locale[]> => {
          const url = router.generate('pim_enrich_locale_rest_index', {activated: true});

          const response = await fetch(url, {
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
            method: 'GET',
          });

          return await response.json();
        },
      },
    }),
    [router, fetchFamilies, fetchAllFamilyLabelsRecursively]
  );

  return <FetcherContext.Provider value={fetcherValue}>{children}</FetcherContext.Provider>;
};

export {PimFetcherProvider};
