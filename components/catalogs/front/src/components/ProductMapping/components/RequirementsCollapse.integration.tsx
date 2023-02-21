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
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.minLength')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.maxLength')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.pattern')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.minimum')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.maximum')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.requirements.constraints.enum')
    ).not.toBeInTheDocument();
});
