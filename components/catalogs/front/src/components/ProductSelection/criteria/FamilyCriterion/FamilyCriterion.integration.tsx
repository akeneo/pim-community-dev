import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {FamilyCriterion} from './FamilyCriterion';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';

jest.mock('../../hooks/useOperatorTranslator');

test('it renders the selected families', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FamilyCriterion
                    state={{field: 'family', operator: Operator.IN_LIST, value: ['foo']}}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.criteria.family.label')).toBeInTheDocument();
    expect(screen.getByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(screen.getByText('foo')).toBeInTheDocument();
});

test('it renders inputs with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FamilyCriterion
                    state={{field: 'family', operator: Operator.IN_LIST, value: ['foo']}}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{
                        field: undefined,
                        operator: 'Invalid operator.',
                        value: 'Invalid value.',
                    }}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(screen.getByText('Invalid operator.')).toBeInTheDocument();
    expect(screen.getByText('Invalid value.')).toBeInTheDocument();
});

test('it calls onChange when the operator changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FamilyCriterion
                    state={{field: 'family', operator: Operator.IN_LIST, value: ['foo']}}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    const container = screen.getByTestId('operator');

    fireEvent.click(within(container).getByRole('textbox'));
    fireEvent.click(screen.getByText(Operator.IS_EMPTY));

    expect(onChange).toHaveBeenCalledWith({
        field: 'family',
        operator: Operator.IS_EMPTY,
        value: [],
    });
});

test('it calls onChange when the value changes', async () => {
    fetchMock.mockResponses(
        // useFamiliesByCodes
        JSON.stringify([
            {
                code: 'foo',
                label: 'Foo',
            },
        ]),
        // useInfiniteFamilies
        JSON.stringify([
            {
                code: 'foo',
                label: 'Foo',
            },
            {
                code: 'bar',
                label: 'Bar',
            },
        ])
    );
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FamilyCriterion
                    state={{field: 'family', operator: Operator.IN_LIST, value: ['foo']}}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    const container = screen.getByTestId('value');
    const input = within(container).getByRole('textbox');

    fireEvent.focus(input);
    expect(await screen.findByText('Bar')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Bar'));

    expect(onChange).toHaveBeenCalledWith({
        field: 'family',
        operator: Operator.IN_LIST,
        value: ['foo', 'bar'],
    });
});
