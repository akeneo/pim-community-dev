'use strict';

// We should honestly avoid this but the channelFetcher expect a jquery Deferred object,
// We haven't find a way to correctly mock it.
import $ from 'jquery';

import {fetchChannels} from 'akeneoassetmanager/infrastructure/fetcher/channel';

describe('akeneoassetmanager/infrastructure/fetcher/channel', () => {
  it('It fetches the channels', async () => {
    const channelFetcher = {
      fetchAll: jest.fn().mockImplementationOnce(() =>
        $.Deferred().resolve([
          {
            code: 'ecommerce',
            locales: [
              {
                code: 'en_US',
                label: 'English (United States)',
                region: 'United States',
                language: 'English',
              },
              {
                code: 'fr_FR',
                label: 'French (France)',
                region: 'France',
                language: 'French',
              },
            ],
            labels: {
              en_US: 'Ecommerce',
            },
          },
          {
            code: 'mobile',
            locales: [
              {
                code: 'en_US',
                label: 'English (United States)',
                region: 'United States',
                language: 'English',
              },
              {
                code: 'fr_FR',
                label: 'French (France)',
                region: 'France',
                language: 'French',
              },
            ],
            labels: {
              en_US: 'Mobile',
            },
          },
        ])
      ),
    };

    const response = await fetchChannels(channelFetcher)();

    expect(response).toEqual([
      {
        code: 'ecommerce',
        locales: [
          {
            code: 'en_US',
            label: 'English (United States)',
            region: 'United States',
            language: 'English',
          },
          {
            code: 'fr_FR',
            label: 'French (France)',
            region: 'France',
            language: 'French',
          },
        ],
        labels: {
          en_US: 'Ecommerce',
        },
      },
      {
        code: 'mobile',
        locales: [
          {
            code: 'en_US',
            label: 'English (United States)',
            region: 'United States',
            language: 'English',
          },
          {
            code: 'fr_FR',
            label: 'French (France)',
            region: 'France',
            language: 'French',
          },
        ],
        labels: {
          en_US: 'Mobile',
        },
      },
    ]);
  });
});
