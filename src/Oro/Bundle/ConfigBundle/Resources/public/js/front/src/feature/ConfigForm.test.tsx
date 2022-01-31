import React, { useCallback, useState } from 'react';
import { FetchHookResult, FetchResult, FetchResultFetched, renderHookWithProviders, renderWithProviders } from '@akeneo-pim-community/shared';
import { screen } from '@testing-library/react';


import { ConfigForm } from './ConfigForm'
import { ConfigServicePayloadBackend, ConfigServicePayloadFrontend, ScopedValue } from '../models/ConfigServicePayload';

import { GlobalWithFetchMock } from 'jest-fetch-mock';
import { findRenderedDOMComponentWithTag } from 'react-dom/test-utils';

const customGlobal: GlobalWithFetchMock = global as unknown as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;


function scopedValue<V>(value: V): ScopedValue<V> {
    return {
        value,
        scope: "app",
        use_parent_scope_value: false
    };
}

describe('FOO', () => {
    test('BAz', async () => {

        fetchMock.mockOnceIf(request => request.url === 'oro_config_configuration_system_get',
            JSON.stringify({
                pim_ui___language: scopedValue('fr-FR'),
                pim_analytics___version_update: scopedValue(true),
                pim_ui___loading_message_enabled: scopedValue(false),
                pim_ui___loading_messages: scopedValue("FOO")
            }))

        fetchMock.mockOnceIf(request => request.url === 'pim_localization_locale_index',
            JSON.stringify([
                {
                    "id": 58,
                    "code": "en_US",
                    "label": "English (United States)",
                    "region": "United States",
                    "language": "English"
                },
                {
                    "id": 90,
                    "code": "fr_FR",
                    "label": "French (France)",
                    "region": "France",
                    "language": "French"
                },

            ]))

        // jest.mock('@akeneo-pim-community/shared', () => ({
        //     useFetchSimpler: () => {
        //         console.log('='.repeat(80))
        //         throw 'FOO'
        //         let result: FetchResult<ConfigServicePayloadFrontend> = { type: 'idle' };
        //         const doFetch = async () => {
        //             result = { type: 'fetching' };

        //             await new Promise((resolve) => {
        //                 setTimeout(resolve, 0)
        //             });
        //             result = {
        //                 type: 'fetched',
        //                 payload: {
        //                     pim_ui___language: mockScopedValue('fr-FR'),
        //                     pim_analytics___version_update: mockScopedValue(true),
        //                     pim_ui___loading_message_enabled: mockScopedValue(false),
        //                     pim_ui___loading_messages: mockScopedValue("FOO")
        //                 }
        //             };

        //         }
        //         return [result, doFetch];
        //     }
        // }))

        renderWithProviders(<ConfigForm />);

        // const loadingMEssageEnablerLabelElt = await screen.findByText('oro_config.form.config.group.loading_message.fields.enabler.label')

        const loadingMessageEnablerLabelElt = await screen.findByTestId('loading_message__enabler')

        // const { result, waitForNextUpdate } = renderHookWithProviders<{}, FetchHookResult<ConfigServicePayloadFrontend>>(() => {
        //     const [result, setResult] = useState<FetchResult<ConfigServicePayloadFrontend>>({ type: 'idle' });
        //     const doFetch = async () => {
        //         setResult({ type: 'fetching' });

        //         await new Promise((resolve) => {
        //             setTimeout(resolve, 100)
        //         });
        //         setResult({
        //             type: 'fetched', payload: {
        //                 pim_ui___language: scopedValue('fr-FR'),
        //                 pim_analytics___version_update: scopedValue(true),
        //                 pim_ui___loading_message_enabled: scopedValue(true),
        //                 pim_ui___loading_messages: scopedValue("FOO")
        //             }
        //         });

        //     }
        //     return [result, doFetch];
        // })

        // expect(result.current[0].type).toEqual('idle');

        // await result.current[1]()
        // await waitForNextUpdate()

        // expect(result.current[0].type).toEqual('fetching');

        // await waitForNextUpdate()

        // expect(result.current[0].type).toEqual('fetched');
        // expect((result.current[0] as FetchResultFetched<ConfigServicePayloadFrontend>).payload).toEqual({});

        //        expect(screen.getByText(112)).toBeInTheDocument();
    })
})

// test('it renders key figure of type count', () => {

// });

// test('it does not render key figure of type count when value is an object', () => {
//   const countAttributes: CatalogVolume = {
//     name: 'count_attributes',
//     type: 'count',
//     value: {
//       average: 4,
//       max: 43,
//     },
//   };

//   renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={countAttributes} />);

//   expect(screen.queryByText('count_attributes.axis.count_attributes')).not.toBeInTheDocument();
// });

// test('it renders key figure of type average', () => {
//   const catalogVolumeAverageMaxAttributesPerFamily: CatalogVolume = {
//     name: 'average_max_attributes_per_family',
//     type: 'average_max',
//     value: {
//       average: 4,
//       max: 43,
//     },
//   };

//   renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={catalogVolumeAverageMaxAttributesPerFamily} />);

//   expect(typeof catalogVolumeAverageMaxAttributesPerFamily.value).toBe('object');
//   expect(typeof (catalogVolumeAverageMaxAttributesPerFamily.value as AverageMaxValue).average).not.toBeUndefined();
//   expect(screen.getByText('pim_catalog_volume.axis.average_max_attributes_per_family')).toBeInTheDocument();
//   expect(screen.getByText(43)).toBeInTheDocument();
// });

// test('it does not render key figure of type average when the value is not an object', () => {
//   const catalogVolumeAverageMaxAttributesPerFamilyWrongFormat: CatalogVolume = {
//     name: 'average_max_attributes_per_family',
//     type: 'average_max',
//     value: 4,
//   };

//   renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={catalogVolumeAverageMaxAttributesPerFamilyWrongFormat} />);

//   expect(screen.queryByText('pim_catalog_volume.axis.average_max_attributes_per_family')).not.toBeInTheDocument();
// });
