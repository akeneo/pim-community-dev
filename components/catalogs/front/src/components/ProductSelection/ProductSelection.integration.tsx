import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {Operator} from './models/Operator';
import {generateRandomId} from './utils/generateRandomId';
import {mocked} from 'ts-jest/utils';
import {StatusCriterionState} from './criteria/StatusCriterion';
import {QueryClient, QueryClientProvider} from 'react-query';
import {ProductSelectionErrors} from './models/ProductSelectionErrors';
import {mockFetchResponses} from '../../../tests/mockFetchResponses';

jest.mock('./utils/generateRandomId');

const MAX_CRITERIA_PER_CATALOG = 25;

test('it displays an empty message if there is no criteria', () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={{}} onChange={jest.fn()} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.empty')).toBeInTheDocument();
});

test('it renders a list of criteria', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
    ]);

    const criteria = {
        qxgJvh: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
        w9WgXc: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: false,
        } as StatusCriterionState,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={jest.fn()} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findAllByText('akeneo_catalogs.product_selection.criteria.status.label')).toHaveLength(2);
    expect(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label')).toBeEnabled();
});

test('it renders a list of criteria with validation errors', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
    ]);

    const criteria = {
        qxgJvh: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
        w9WgXc: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: false,
        } as StatusCriterionState,
    };

    const errors: ProductSelectionErrors = {
        qxgJvh: {
            field: undefined,
            operator: undefined,
            value: undefined,
            locale: undefined,
            scope: undefined,
        },
        w9WgXc: {
            field: undefined,
            operator: undefined,
            value: 'Some random error message',
            locale: undefined,
            scope: undefined,
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={jest.fn()} errors={errors} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('Some random error message')).toBeInTheDocument();
});

test('it updates the state when a criterion is added', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
    ]);

    mocked(generateRandomId).mockReturnValue('qxgJvh');

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={{}} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label'));
    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label'));

    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label')).toBeInTheDocument();
    expect(onChange).toHaveBeenCalledWith({
        qxgJvh: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    });
});

test('it updates the state when a criterion changes', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
    ]);

    const criteria = {
        qxgJvh: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
    };
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    const StatusValueInput = await screen.findByTestId('value');

    fireEvent.click(within(StatusValueInput).getByRole('textbox'));
    fireEvent.click(screen.getByText('akeneo_catalogs.product_selection.criteria.status.disabled'));

    expect(onChange).toHaveBeenCalledWith({
        qxgJvh: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: false,
        },
    });
});

test('it updates the state when a criterion is removed', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
    ]);

    const criteria = {
        qxgJvh: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
    };
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByTitle('remove'));

    expect(onChange).toHaveBeenCalledWith({});
});

test('it shows a warning and lock the add button when the criteria limit is reached', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
    ]);

    const criteria = [];
    for (let criterionIndex = 0; criterionIndex < MAX_CRITERIA_PER_CATALOG; criterionIndex++) {
        criteria[criterionIndex] = {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        };
    }

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={jest.fn()} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.max_reached')).toBeInTheDocument();
    expect(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label')).toBeDisabled();
});
