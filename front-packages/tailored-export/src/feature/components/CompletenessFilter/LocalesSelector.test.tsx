import {act, screen} from '@testing-library/react';
import {Channel, renderWithProviders as baseRender} from '@akeneo-pim-community/shared';
import {LocalesSelector} from "./LocalesSelector";
import React, {ReactNode} from "react";
import {FetcherContext} from "../../contexts";

const channels: Channel[] = [
    {
        code: 'ecommerce',
        labels: {},
        locales: [
            {
                code: 'en_US',
                label: 'English (American)',
                region: 'US',
                language: 'en',
            },
            {
                code: 'fr_FR',
                label: 'French',
                region: 'FR',
                language: 'fr',
            },
        ],
        category_tree: '',
        conversion_units: [],
        currencies: [],
        meta: {
            created: '',
            form: '',
            id: 1,
            updated: '',
        },
    },
];
const fetchers = {
    channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve(channels)},
};
const renderWithProviders = async (node: ReactNode) =>
    await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node})</FetcherContext.Provider>));

test('it displays the selected locales', async () =>{
    renderWithProviders(<LocalesSelector locales={['en_US']} onChange={() => {}}/>);

    expect(screen.getByText('pim_connector.export.completeness.locale_selector.label')).toBeInTheDocument();
    expect(screen.getByText('English (American)')).toBeInTheDocument();
})

// test('it notifies when the operator is changed', async () =>{
//     renderWithProviders(<LocalesSelector locales={['en_US']} onChange={() => {}}/>);
//
//     expect(screen.getByText('pim_connector.export.completeness.locale_selector.label')).toBeInTheDocument();
//     expect(screen.getByText('English (American)')).toBeInTheDocument();
// })
