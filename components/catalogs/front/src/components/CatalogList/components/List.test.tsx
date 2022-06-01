jest.unmock('./List');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {List} from './List';
import {useCatalogs} from '../hooks/useCatalogs';

test('it renders without error', () => {
    (useCatalogs as jest.Mock).mockReturnValue({
        isLoading: false,
        isError: false,
        data: [
            {
                id: '123e4567-e89b-12d3-a456-426614174000',
                name: 'store US',
                enabled: true,
            },
        ],
    });

    render(
        <ThemeProvider theme={pimTheme}>
            <List owner={'username'} />
        </ThemeProvider>
    );

    expect(screen.getByText('store US')).toBeInTheDocument();
});

test('it renders with no catalogs', () => {
    (useCatalogs as jest.Mock).mockReturnValue({
        isLoading: false,
        isError: false,
        data: [],
    });

    render(
        <ThemeProvider theme={pimTheme}>
            <List owner={'username'} />
        </ThemeProvider>
    );

    expect(screen.getByText('Empty')).toBeInTheDocument();
});

test('it renders nothing when catalogs are in loading', () => {
    (useCatalogs as jest.Mock).mockReturnValue({
        isLoading: true,
        isError: false,
        data: [],
    });

    const {container} = render(
        <ThemeProvider theme={pimTheme}>
            <List owner={'username'} />
        </ThemeProvider>
    );

    expect(container).toBeEmptyDOMElement();
});

test('it throws an error when the API call failed', () => {
    (useCatalogs as jest.Mock).mockReturnValue({
        isLoading: false,
        isError: true,
        data: [],
    });

    // mute the error in the output
    jest.spyOn(console, 'error');
    (console.error as jest.Mock).mockImplementation(() => {});

    expect(() => {
        render(
            <ThemeProvider theme={pimTheme}>
                <List owner={'username'} />
            </ThemeProvider>
        );
    }).toThrow(Error);
});
