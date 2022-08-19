import React from 'react';
import {fireEvent, render, screen, waitFor, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {CategoryCriterion} from './CategoryCriterion';

jest.mock('../../hooks/useOperatorTranslator');

const changeOperatorValueTo = (operator: string) => {
    const operatorSelect = screen.getByTestId('operator');
    fireEvent.click(within(operatorSelect).getByRole('textbox'));
    fireEvent.click(screen.getByText(operator));
};

test('it renders the selected categories', async () => {
    const categories = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(categories));

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB']}}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.criteria.category.label')).toBeInTheDocument();
    expect(screen.getByText(Operator.IN_LIST)).toBeInTheDocument();

    await waitFor(() => screen.findByText('[catA]'));

    expect(screen.getByText('[catA]')).toBeInTheDocument();
    expect(screen.getByText('[catB]')).toBeInTheDocument();
});

test('it renders inputs with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB']}}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{
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

test('it calls onRemove when criterion is removed', () => {
    const onRemove = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB']}}
                    onChange={jest.fn()}
                    onRemove={onRemove}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByTitle('akeneo_catalogs.product_selection.action.remove'));

    expect(onRemove).toHaveBeenCalled();
});

test('it calls onChange when the operator changes', () => {
    const categories = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(categories));

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB']}}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    changeOperatorValueTo(Operator.IN_CHILDREN_LIST);

    expect(onChange).toHaveBeenCalledWith({
        field: 'categories',
        operator: Operator.IN_CHILDREN_LIST,
        value: ['catA', 'catB'],
    });
});

test('it hides value field and resets selected value when operator value is unclassified', async () => {
    const categories = [
        {
            code: 'catA',
            label: 'Category A',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: 'Category B',
            isLeaf: true,
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(categories));
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB']}}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    await waitFor(() => screen.findByText('Category A'));

    changeOperatorValueTo(Operator.UNCLASSIFIED);

    expect(onChange).toHaveBeenCalledWith({
        field: 'categories',
        operator: Operator.UNCLASSIFIED,
        value: [],
    });
});

test('it calls onChange with remaining items when removing category from selection ', async () => {
    const categories = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
        {
            code: 'catC',
            label: '[catC]',
            isLeaf: false,
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(categories));
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB', 'catC']}}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    await waitFor(() => screen.findByText('[catB]'));

    const selectedCategory = screen.getByTestId('catB');
    const deleteIcon = within(selectedCategory).getByTitle(
        'akeneo_catalogs.product_selection.criteria.category.remove'
    );

    fireEvent.click(deleteIcon);

    expect(onChange).toHaveBeenCalledWith({
        field: 'categories',
        operator: Operator.IN_LIST,
        value: ['catA', 'catC'],
    });
});

test('it calls onChange with categories selected from category tree selector ', async () => {
    const categories = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
    ];

    const categoryTree = [
        {
            code: 'master',
            label: '[master]',
            isLeaf: false,
        },
        {
            code: 'print',
            label: '[print]',
            isLeaf: false,
        },
    ];

    const masterChildren = [
        {
            code: 'childA',
            label: '[childA]',
            isLeaf: true,
        },
        {
            code: 'childB',
            label: '[childB]',
            isLeaf: false,
        },
    ];

    fetchMock.mockResponses(
        //useCategories with catA, catB, and catC
        JSON.stringify(categories),
        //useCategoryTrees
        JSON.stringify(categoryTree),
        //useChildren for master
        JSON.stringify(masterChildren)
    );

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB']}}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    await waitFor(() => screen.findByText('[catB]'));

    const categorySelection = screen.getByTestId('category-selection');
    fireEvent.click(categorySelection);

    const categoryTreeContainer = await screen.findByTestId('category-tree');
    expect(categoryTreeContainer).toBeInTheDocument();

    const masterCheckboxes = within(categoryTreeContainer).getAllByRole('checkbox');
    fireEvent.click(masterCheckboxes[2]); //click on childB

    expect(onChange).toHaveBeenCalledWith({
        field: 'categories',
        operator: Operator.IN_LIST,
        value: ['catA', 'catB', 'childB'],
    });

    await waitFor(() => screen.getByText('[childB]'), {
        container: categorySelection,
    });
});

test('it calls onChange with categories selected from a different tree selector ', async () => {
    const categories = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
    ];

    const categoryTree = [
        {
            code: 'master',
            label: '[master]',
            isLeaf: false,
        },
        {
            code: 'print',
            label: '[print]',
            isLeaf: false,
        },
    ];

    const masterChildren = [
        {
            code: 'childA',
            label: '[childA]',
            isLeaf: true,
        },
        {
            code: 'childB',
            label: '[childB]',
            isLeaf: false,
        },
    ];

    const printChildren = [
        {
            code: 'childC',
            label: '[childC]',
            isLeaf: false,
        },
    ];

    fetchMock.mockResponses(
        //useCategories with catA, catB, and catC
        JSON.stringify(categories),
        //useCategoryTrees
        JSON.stringify(categoryTree),
        //useChildren for master
        JSON.stringify(masterChildren),
        //useChildren for print
        JSON.stringify(printChildren)
    );

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CategoryCriterion
                    state={{field: 'categories', operator: Operator.IN_LIST, value: ['catA', 'catB']}}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    await waitFor(() => screen.findByText('[catB]'));

    const categorySelection = screen.getByTestId('category-selection');
    fireEvent.click(categorySelection);

    const categoryTreeContainer = await screen.findByTestId('category-tree');
    expect(categoryTreeContainer).toBeInTheDocument();

    const treeSelectorContainer = screen.getByTestId('category-tree-selector');
    fireEvent.click(within(treeSelectorContainer).getByText('[master]'));

    expect(await screen.findByText('[print]')).toBeInTheDocument();

    fireEvent.click(screen.getByText('[print]'));

    expect(await screen.findByText('[childC]')).toBeInTheDocument();

    const masterCheckboxes = within(categoryTreeContainer).getAllByRole('checkbox');
    fireEvent.click(masterCheckboxes[1]); //click on childC

    expect(onChange).toHaveBeenCalledWith({
        field: 'categories',
        operator: Operator.IN_LIST,
        value: ['catA', 'catB', 'childC'],
    });

    await waitFor(() => screen.getByText('[childC]'), {
        container: categorySelection,
    });
});
