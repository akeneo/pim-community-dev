import React, {FC, useMemo} from 'react';
import {FetcherContext} from '../contexts';
import {TimeToEnrich} from '../../TimeToEnrich';
import {useRouter} from '@akeneo-pim-community/shared';
import {Channel, ChannelCode, Family, FamilyCode, Locale, LocaleCode} from '../models';

const PimFetcherProvider: FC = ({children}) => {
  const router = useRouter();
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
      },
      family: {
        fetchFamilies: async (limit: number, page: number, search?: string): Promise<{[key: string]: Family}> => {
          const params = {
            options: {
              limit: 20,
              page: page,
              expanded: 0,
            },
          };

          if (search !== undefined && search.trim().length > 0) {
            params['search'] = search;
          }

          const url = router.generate('pim_enrich_family_rest_index', params);

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
    [router]
  );

  return <FetcherContext.Provider value={fetcherValue}>{children}</FetcherContext.Provider>;
};

export {PimFetcherProvider};
