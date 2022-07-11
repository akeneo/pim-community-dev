jest.unmock('./AddCriterionDropdown');
jest.unmock('../contexts/ProductSelectionContext');

import React from 'react';
import {act, fireEvent, render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {mocked} from 'ts-jest/utils';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AddCriterionDropdown} from './AddCriterionDropdown';
import {ProductSelectionContext} from '../contexts/ProductSelectionContext';
import {Operator} from '../models/Operator';
import {useCriteriaRegistry} from '../hooks/useCriteriaRegistry';

mocked(useCriteriaRegistry).mockImplementation(() => ({
    system: [
        {
            label: 'akeneo_catalogs.product_selection.criteria.status.label',
            factory: () => ({
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            }),
        },
    ],
    getCriterionByField: () => Promise.reject(),
}));

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <AddCriterionDropdown />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label')).toBeInTheDocument();
});

// test('it opens the dropdown and adds a criterion', () => {
//     const dispatch = jest.fn();
//
//     render(
//         <ThemeProvider theme={pimTheme}>
//             <ProductSelectionContext.Provider value={dispatch}>
//                 <AddCriterionDropdown />
//             </ProductSelectionContext.Provider>
//         </ThemeProvider>
//     );
//
//     act(() => userEvent.click(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label')));
//     act(() => userEvent.click(screen.getByText('akeneo_catalogs.product_selection.criteria.status.label')));
//
//     expect(dispatch).toHaveBeenCalledWith(
//         expect.objectContaining({
//             type: 'ADD_CRITERION',
//             id: expect.any(String),
//             state: {
//                 field: 'enabled',
//                 operator: Operator.EQUALS,
//                 value: true,
//             },
//         })
//     );
// });
//
// test('it opens and closes the dropdown', () => {
//     render(
//         <ThemeProvider theme={pimTheme}>
//             <AddCriterionDropdown />
//         </ThemeProvider>
//     );
//
//     fireEvent.click(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label'));
//     expect(screen.getByText('akeneo_catalogs.product_selection.add_criteria.section_system')).toBeInTheDocument();
//     fireEvent.click(screen.getByTestId('backdrop'));
//     expect(screen.queryByText('akeneo_catalogs.product_selection.add_criteria.section_system')).not.toBeInTheDocument();
// });
//
// test('it opens and searches in the options', () => {
//     render(
//         <ThemeProvider theme={pimTheme}>
//             <AddCriterionDropdown />
//         </ThemeProvider>
//     );
//
//     fireEvent.click(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label'));
//     expect(screen.getByText('akeneo_catalogs.product_selection.add_criteria.section_system')).toBeInTheDocument();
//     fireEvent.change(screen.getByRole('textbox'), {target: {value: 'not_a_valid_option'}});
//     expect(screen.queryByText('akeneo_catalogs.product_selection.add_criteria.section_system')).not.toBeInTheDocument();
// });
