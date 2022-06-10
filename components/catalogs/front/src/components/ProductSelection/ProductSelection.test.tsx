jest.unmock('./ProductSelection');

import userEvent from '@testing-library/user-event';
import React from 'react';
import {act, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {useCatalogCriteria} from './hooks/useCatalogCriteria';
import {Operator} from './models/Operator';

test('it renders the empty message', () => {
    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => []);

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(screen.getByText('[Empty]')).toBeInTheDocument();
});

test('it renders a list of criteria', () => {
    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => [
        {
            id: 'foo',
            module: () => <div>[FooCriteria]</div>,
            field: 'foo',
            operator: Operator.EQUALS,
            value: '',
        },
        {
            id: 'bar',
            module: () => <div>[BarCriteria]</div>,
            field: 'bar',
            operator: Operator.EQUALS,
            value: '',
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(screen.getByText('[FooCriteria]')).toBeInTheDocument();
    expect(screen.getByText('[BarCriteria]')).toBeInTheDocument();
});

test('it updates the state when a criterion changes', () => {
    const FakeCriterionModule = jest.fn(({onChange}) => (
        <button onClick={() => onChange({operator: '=', value: 'bar'})}>[FakeCriterionButton]</button>
    ));

    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => [
        {
            id: 'foo',
            module: FakeCriterionModule,
            field: 'foo',
            operator: Operator.EQUALS,
            value: '',
        },
        {
            id: 'bar',
            module: () => <div>[BarCriteria]</div>,
            field: 'bar',
            operator: Operator.EQUALS,
            value: '',
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(FakeCriterionModule).toHaveBeenCalledWith(
        {
            onChange: expect.any(Function),
            value: expect.objectContaining({
                operator: Operator.EQUALS,
                value: '',
            }),
        },
        {}
    );

    act(() => userEvent.click(screen.getByText('[FakeCriterionButton]')));

    expect(FakeCriterionModule).toHaveBeenCalledWith(
        {
            onChange: expect.any(Function),
            value: expect.objectContaining({
                operator: Operator.EQUALS,
                value: 'bar',
            }),
        },
        {}
    );
});
