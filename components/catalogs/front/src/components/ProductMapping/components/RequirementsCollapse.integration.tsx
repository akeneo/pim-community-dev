import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {RequirementsCollapse} from './RequirementsCollapse';
import {Target} from '../models/Target';

test('it returns null if there is nothing to display', () => {
    const targetWithoutDescriptionAndRequirements: Target = {
        code: 'erp_name',
        label: 'ERP name',
        type: 'string',
        format: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <RequirementsCollapse target={targetWithoutDescriptionAndRequirements}></RequirementsCollapse>
        </ThemeProvider>
    );

    expect(screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.title')).not.toBeInTheDocument();
});

test('it display the description of the target', () => {
    const target: Target = {
        code: 'erp_name',
        label: 'ERP name',
        type: 'string',
        description: 'Name description',
        format: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <RequirementsCollapse target={target}></RequirementsCollapse>
        </ThemeProvider>
    );

    expect(screen.queryByText('Name description')).toBeInTheDocument();
});

test('it display the description of the target', () => {
    const target: Target = {
        code: 'erp_name',
        label: 'ERP name',
        type: 'string',
        description: 'ERP name description',
        format: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <RequirementsCollapse target={target}></RequirementsCollapse>
        </ThemeProvider>
    );

    expect(screen.queryByText('ERP name description')).toBeInTheDocument();
});

test('it displays string type requirements', () => {
    const target: Target = {
        code: 'erp_name',
        label: 'ERP name',
        type: 'string',
        format: null,
        minLength: 3,
        maxLength: 50,
        pattern: '[a-zA-Z].',
        enum: ['S', 'M', 'L'],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <RequirementsCollapse target={target}></RequirementsCollapse>
        </ThemeProvider>
    );

    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.minLength')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.maxLength')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.pattern')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.enum')
    ).toBeInTheDocument();
});

test('it displays number type requirements', () => {
    const target: Target = {
        code: 'weight',
        label: 'Weight',
        type: 'number',
        format: null,
        minimum: 0,
        maximum: 1000,
        enum: [1, 2, 3],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <RequirementsCollapse target={target}></RequirementsCollapse>
        </ThemeProvider>
    );

    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.minimum')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.maximum')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.enum')
    ).toBeInTheDocument();
});

test('it does not display warnings if there is only a description', () => {
    const targetWithoutDescriptionAndRequirements: Target = {
        code: 'erp_name',
        label: 'ERP name',
        type: 'string',
        format: null,
        description: 'Description',
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <RequirementsCollapse target={targetWithoutDescriptionAndRequirements}></RequirementsCollapse>
        </ThemeProvider>
    );

    expect(screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.title')).toBeInTheDocument();
    expect(screen.queryByText('Description')).toBeInTheDocument();

    expect(
        screen.queryAllByText('akeneo_catalogs.product_mapping.source.requirements.constraints', {exact: false})
    ).toHaveLength(0);
});
