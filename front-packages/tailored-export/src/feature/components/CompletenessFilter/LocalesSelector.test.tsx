import {act, screen} from '@testing-library/react';
import {Channel, renderWithProviders as baseRender} from '@akeneo-pim-community/shared';
import {LocalesSelector} from "./LocalesSelector";
import React, {ReactNode} from "react";
import {FetcherContext} from "../../contexts";
import userEvent from "@testing-library/user-event";

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
    await renderWithProviders(<LocalesSelector locales={['en_US']} onChange={() => {}}/>);

    expect(screen.queryByText('pim_connector.export.completeness.locale_selector.label')).toBeInTheDocument();
    expect(screen.queryByText('English (American)')).toBeInTheDocument();
})

test('it notifies when a locale is added to the selection', async () => {
    const onLocalesSelectionChange = jest.fn();
    await renderWithProviders(<LocalesSelector locales={['fr_FR']} onChange={onLocalesSelectionChange} />);

    userEvent.click(screen.getByText('pim_connector.export.completeness.locale_selector.label'));
    userEvent.click(screen.getByText('English (American)'));

    expect(onLocalesSelectionChange).toHaveBeenCalledWith(['fr_FR', 'en_US']);
})
