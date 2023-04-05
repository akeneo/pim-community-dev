import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme, Table} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {TargetSourceAssociation} from './TargetSourceAssociation';

test('it displays target code when there is no label', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'color'}
                        onClick={onClick}
                        targetCode={'color'}
                        targetLabel={undefined}
                        source={source}
                        hasError={false}
                        isRequired={false}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.getByText('color')).toBeInTheDocument();
    expect(screen.queryByTestId('required-pill')).not.toBeInTheDocument();
});

test('it displays target label when provided instead of target code', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'color'}
                        onClick={onClick}
                        targetCode={'color'}
                        targetLabel={'Color'}
                        source={source}
                        hasError={false}
                        isRequired={false}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.queryByText('color')).not.toBeInTheDocument();
    expect(screen.getByText('Color')).toBeInTheDocument();
});

test('it displays a placeholder when source is null and no default value', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'color'}
                        onClick={onClick}
                        targetCode={'color'}
                        targetLabel={'Color'}
                        source={source}
                        hasError={false}
                        isRequired={true}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_mapping.target.table.placeholder')).toBeInTheDocument();
});

test('it displays UUID when the targetCode is uuid', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'uuid'}
                        onClick={onClick}
                        targetCode={'uuid'}
                        targetLabel={'Uuid'}
                        source={source}
                        hasError={false}
                        isRequired={false}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.getByText('UUID')).toBeInTheDocument();
});

test('it displays a placeholder for source when null and no default value', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'color'}
                        onClick={onClick}
                        targetCode={'color'}
                        targetLabel={'Color'}
                        source={source}
                        hasError={false}
                        isRequired={false}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_mapping.target.table.placeholder')).toBeInTheDocument();
});

test('it displays the source default value when defined', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
        default: 'red',
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'color'}
                        onClick={onClick}
                        targetCode={'color'}
                        targetLabel={'color'}
                        source={source}
                        hasError={false}
                        isRequired={false}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.getByText('red', {exact: false})).toBeInTheDocument();
});

test('it displays the source default boolean value capitalized', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
        default: true,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'is_released'}
                        onClick={onClick}
                        targetCode={'is_released'}
                        targetLabel={'Is released'}
                        source={source}
                        hasError={false}
                        isRequired={false}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.getByText('True', {exact: false})).toBeInTheDocument();
});

test('it displays the source when defined', async () => {
    const onClick = jest.fn();

    const source = {
        source: 'color',
        locale: null,
        scope: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <Table>
                    <Table.Body>
                        <TargetSourceAssociation
                            isSelected={true}
                            key={'color'}
                            onClick={onClick}
                            targetCode={'color'}
                            targetLabel={'Color'}
                            source={source}
                            hasError={false}
                            isRequired={false}
                        />
                    </Table.Body>
                </Table>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('[color]')).toBeInTheDocument();
});

test('it displays an error pill when there is an error', () => {
    const onClick = jest.fn();

    const source = {
        source: null,
        locale: null,
        scope: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Table>
                <Table.Body>
                    <TargetSourceAssociation
                        isSelected={true}
                        key={'color'}
                        onClick={onClick}
                        targetCode={'color'}
                        targetLabel={'Color'}
                        source={source}
                        hasError={true}
                        isRequired={false}
                    />
                </Table.Body>
            </Table>
        </ThemeProvider>
    );

    expect(screen.getByTestId('error-pill')).toBeInTheDocument();
});
