import React, {MutableRefObject, useLayoutEffect, useRef, useState} from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogEdit} from './CatalogEdit';
import {CatalogFormContext} from './contexts/CatalogFormContext';
import {CatalogFormActions} from './reducers/CatalogFormReducer';
import {Operator} from '../ProductSelection';
import {mocked} from 'ts-jest/utils';
import {generateRandomId} from '../ProductSelection/utils/generateRandomId';
import {QueryClient, QueryClientProvider} from 'react-query';
import {mockFetchResponses} from '../../../tests/mockFetchResponses';

jest.mock('../ProductSelection/utils/generateRandomId');

const openDropdown = (selector: string): void => {
    const container = screen.getByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

test('it can enable a catalog', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/a134c164-9343-4796-9b4e-e2c04ba3765a',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: 0,
        },
    ]);

    const dispatch = jest.fn();
    const form = {
        values: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
        dispatch: dispatch,
        errors: [],
    };

    function RenderWithHeaderContextContainer() {
        const ref = useRef<HTMLDivElement>() as MutableRefObject<HTMLDivElement>;
        const [headerContextContainer, setHeaderContextContainer] = useState<HTMLDivElement | undefined>(undefined);
        useLayoutEffect(() => {
            setHeaderContextContainer(ref.current);
        }, [ref]);

        return (
            <ThemeProvider theme={pimTheme}>
                <div ref={ref} />
                <QueryClientProvider client={new QueryClient()}>
                    <CatalogFormContext.Provider value={dispatch}>
                        <CatalogEdit
                            id={'a134c164-9343-4796-9b4e-e2c04ba3765a'}
                            form={form}
                            headerContextContainer={headerContextContainer}
                        />
                    </CatalogFormContext.Provider>
                </QueryClientProvider>
            </ThemeProvider>
        );
    }

    render(<RenderWithHeaderContextContainer />);

    fireEvent.click(await screen.findByText('akeneo_catalogs.catalog_status_widget.fields.enable_catalog'));
    fireEvent.click(await screen.findByText('akeneo_catalogs.catalog_status_widget.inputs.yes'));
    expect(dispatch).toHaveBeenCalledWith({type: CatalogFormActions.SET_ENABLED, value: true});
});

test('it can change criteria in the product selection', async () => {
    mocked(generateRandomId).mockReturnValue('rdn');

    mockFetchResponses([
        {
            url: '/rest/catalogs/a134c164-9343-4796-9b4e-e2c04ba3765a',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: 0,
        },
    ]);

    const dispatch = jest.fn();
    const form = {
        values: {
            enabled: true,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
        dispatch: dispatch,
        errors: [],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <CatalogFormContext.Provider value={dispatch}>
                    <CatalogEdit
                        id={'a134c164-9343-4796-9b4e-e2c04ba3765a'}
                        form={form}
                        headerContextContainer={undefined}
                    />
                </CatalogFormContext.Provider>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.product_selection'));
    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label'));
    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label'));

    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label')).toBeInTheDocument();
    expect(dispatch).toHaveBeenCalledWith({
        type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA,
        value: {
            rdn: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        },
    });
});

test('it can add a product value filter on the channel', async () => {
    const ECOMMERCE = {code: 'ecommerce', label: 'E-commerce'};
    const PRINT = {code: 'print', label: 'Print'};

    mockFetchResponses([
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [ECOMMERCE, PRINT],
        },
        {
            url: '/rest/catalogs/channels?codes=',
            json: [],
        },
        {
            url: '/rest/catalogs/locales?codes=',
            json: [],
        },
        {
            url: '/rest/catalogs/locales?page=1&limit=20',
            json: [],
        },
        {
            url: '/rest/catalogs/currencies',
            json: [],
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=',
            json: [],
        },
        {
            url: '/rest/catalogs/a134c164-9343-4796-9b4e-e2c04ba3765a',
            json: {},
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=pim_catalog_identifier%2Cpim_catalog_text%2Cpim_catalog_textarea%2Cpim_catalog_simpleselect%2Cpim_catalog_multiselect%2Cpim_catalog_number%2Cpim_catalog_metric%2Cpim_catalog_boolean%2Cpim_catalog_date',
            json: {},
        },
        {
            url: '/rest/catalogs/product-selection-criteria/product/count',
            json: 0,
        },
    ]);

    const dispatch = jest.fn();
    const form = {
        values: {
            enabled: true,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
        dispatch: dispatch,
        errors: [],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <CatalogFormContext.Provider value={dispatch}>
                    <CatalogEdit
                        id={'a134c164-9343-4796-9b4e-e2c04ba3765a'}
                        form={form}
                        headerContextContainer={undefined}
                    />
                </CatalogFormContext.Provider>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.product_value_filters'));

    openDropdown('product-value-filter-by-channel');

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('Print')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('E-commerce'));

    expect(dispatch).toHaveBeenCalledWith({
        type: CatalogFormActions.SET_PRODUCT_VALUE_FILTERS,
        value: {channels: ['ecommerce']},
    });
});
