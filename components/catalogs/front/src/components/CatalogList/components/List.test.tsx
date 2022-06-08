jest.unmock('./List');

import React from 'react';
import {act, render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {List} from './List';
import {useCatalogs} from '../hooks/useCatalogs';

test('it renders without error', () => {
    (useCatalogs as unknown as jest.MockedFunction<typeof useCatalogs>).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [
            {
                id: '123e4567-e89b-12d3-a456-426614174000',
                name: 'store US',
                enabled: true,
                owner_username: 'willy',
            },
        ],
        error: null,
    }));

    render(
        <ThemeProvider theme={pimTheme}>
            <List owner={'username'} onCatalogClick={() => {}} />
        </ThemeProvider>
    );

    expect(screen.getByText('store US')).toBeInTheDocument();
});

test('it renders with no catalogs', () => {
    (useCatalogs as unknown as jest.MockedFunction<typeof useCatalogs>).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    render(
        <ThemeProvider theme={pimTheme}>
            <List owner={'username'} onCatalogClick={() => {}} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Empty]')).toBeInTheDocument();
});

test('it calls onCatalogClick whena catalog is clicked', () => {
    (useCatalogs as unknown as jest.MockedFunction<typeof useCatalogs>).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [
            {
                id: '123e4567-e89b-12d3-a456-426614174000',
                name: 'store US',
                enabled: true,
                owner_username: 'willy',
            },
        ],
        error: null,
    }));

    const handleCatalogClick = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <List owner={'username'} onCatalogClick={handleCatalogClick} />
        </ThemeProvider>
    );

    expect(handleCatalogClick).not.toHaveBeenCalled();

    act(() => {
        userEvent.click(screen.getByText('store US'));
    });

    expect(handleCatalogClick).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000');
});

test('it renders nothing when catalogs are in loading', () => {
    (useCatalogs as unknown as jest.MockedFunction<typeof useCatalogs>).mockImplementation(() => ({
        isLoading: true,
        isError: false,
        data: [],
        error: null,
    }));

    const {container} = render(
        <ThemeProvider theme={pimTheme}>
            <List owner={'username'} onCatalogClick={() => {}} />
        </ThemeProvider>
    );

    expect(container).toBeEmptyDOMElement();
});

test('it throws an error when the API call failed', () => {
    (useCatalogs as unknown as jest.MockedFunction<typeof useCatalogs>).mockImplementation(() => ({
        isLoading: false,
        isError: true,
        data: [],
        error: null,
    }));

    // mute the error in the output
    jest.spyOn(console, 'error');
    (console.error as jest.Mock).mockImplementation(() => {});

    expect(() => {
        render(
            <ThemeProvider theme={pimTheme}>
                <List owner={'username'} onCatalogClick={() => {}} />
            </ThemeProvider>
        );
    }).toThrow(Error);
});

test('it throws an error when data field is undefined', () => {
    (useCatalogs as unknown as jest.MockedFunction<typeof useCatalogs>).mockImplementation(() => ({
        isLoading: false,
        isError: true,
        data: undefined,
        error: null,
    }));

    // mute the error in the output
    jest.spyOn(console, 'error');
    (console.error as jest.Mock).mockImplementation(() => {});

    expect(() => {
        render(
            <ThemeProvider theme={pimTheme}>
                <List owner={'username'} onCatalogClick={() => {}} />
            </ThemeProvider>
        );
    }).toThrow(Error);
});
