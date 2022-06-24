jest.unmock('./Edit');
jest.unmock('./TabBar');

import React from 'react';
import {act, render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {mocked} from 'ts-jest/utils';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Edit} from './Edit';
import {CatalogEditRef} from '../CatalogEdit';
import {useSaveCriteria} from '../../ProductSelection/hooks/useSaveCriteria';
import {Operator} from '../../ProductSelection/models/Operator';
import {StatusCriterion} from '../../ProductSelection/criteria/StatusCriterion/types';
import {useEditableCatalogCriteria} from '../hooks/useEditableCatalogCriteria';

jest.mock('../../ProductSelection', () => ({
    ProductSelection: () => <>[ProductSelection]</>,
}));
jest.mock('./Settings', () => ({
    Settings: () => <>[Settings]</>,
}));

test('it renders without error', () => {
    mocked(useEditableCatalogCriteria).mockImplementation(() => [[], jest.fn()]);

    render(
        <ThemeProvider theme={pimTheme}>
            <Edit id={'123e4567-e89b-12d3-a456-426614174000'} onChange={jest.fn()} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Settings]')).toBeInTheDocument();
});

test('it switches between tabs', () => {
    mocked(useEditableCatalogCriteria).mockImplementation(() => [[], jest.fn()]);

    render(
        <ThemeProvider theme={pimTheme}>
            <Edit id={'123e4567-e89b-12d3-a456-426614174000'} onChange={jest.fn()} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Settings]')).toBeInTheDocument();

    act(() => userEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.product_selection')));

    expect(screen.getByText('[ProductSelection]')).toBeInTheDocument();

    act(() => userEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.settings')));

    expect(screen.getByText('[Settings]')).toBeInTheDocument();
});

test('it calls save from parent component', () => {
    const criterion1: StatusCriterion = {
        id: 'foo',
        module: () => <div>[FoorCriterion]</div>,
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    };
    const criterion2: StatusCriterion = {
        id: 'bar',
        module: () => <div>[BarCriterion]</div>,
        state: {
            field: 'enabled',
            operator: Operator.NOT_EQUAL,
            value: false,
        },
    };
    const criteria = [criterion1, criterion2];
    mocked(useEditableCatalogCriteria).mockImplementation(() => [criteria, jest.fn()]);

    const mutate = jest.fn();
    const saveCriteriaResult = {
        isLoading: false,
        isError: false,
        data: undefined,
        error: null,
        mutate: mutate,
    };
    (useSaveCriteria as jest.Mock).mockImplementation(() => saveCriteriaResult);

    const ref: {current: CatalogEditRef | null} = {
        current: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Edit id={'123e4567-e89b-12d3-a456-426614174000'} onChange={jest.fn()} ref={ref} />
        </ThemeProvider>
    );

    expect(ref.current).not.toBeUndefined();

    ref.current && ref.current.save();

    expect(mutate).toHaveBeenCalledWith([
        {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
        {
            field: 'enabled',
            operator: Operator.NOT_EQUAL,
            value: false,
        },
    ]);
});
