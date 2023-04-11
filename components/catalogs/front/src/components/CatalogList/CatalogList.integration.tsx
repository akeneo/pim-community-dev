import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogList} from './CatalogList';
import {ReactQueryWrapper} from '../../../tests/ReactQueryWrapper';

test('it renders a message when there is no catalogs', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CatalogList owner={'shopifi'} onCatalogClick={jest.fn()} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('akeneo_catalogs.catalog_list.empty')).toBeInTheDocument();
});

test('it renders a list of catalogs', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                id: '123e4567-e89b-12d3-a456-426614174000',
                name: 'Store US',
                enabled: true,
                owner_username: 'shopifi',
            },
        ])
    );

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CatalogList owner={'shopifi'} onCatalogClick={jest.fn()} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('Store US')).toBeInTheDocument();
    expect(screen.getByText('akeneo_catalogs.catalog_list.enabled')).toBeInTheDocument();
});

test('it calls the callback when a catalog is clicked', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                id: '123e4567-e89b-12d3-a456-426614174000',
                name: 'Store US',
                enabled: true,
                owner_username: 'shopifi',
            },
        ])
    );

    const handleClick = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CatalogList owner={'shopifi'} onCatalogClick={handleClick} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('Store US'));
    expect(handleClick).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000');
});

test('it renders a message when there is no catalogs', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CatalogList owner={'shopifi'} onCatalogClick={jest.fn()} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('akeneo_catalogs.catalog_list.empty')).toBeInTheDocument();
});
