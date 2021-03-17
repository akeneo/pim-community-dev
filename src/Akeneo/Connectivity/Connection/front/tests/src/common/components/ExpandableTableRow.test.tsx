import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import ExpandableTableRow from '@src/common/components/ExpandableTableRow';
import {renderWithProviders} from '../../../test-utils';
import userEvent from '@testing-library/user-event';

describe('testing expandable table row', () => {
    test('row is not expanded by default', () => {
        const contentToExpand = 'Hello World!';
        renderWithProviders(
            <table>
                <thead>
                    <tr>
                        <th>header 1</th>
                        <th>header 2</th>
                        <th>header 3</th>
                    </tr>
                </thead>
                <tbody>
                    <ExpandableTableRow contentToExpand={contentToExpand}>
                        <td>Cell 1</td>
                        <td>Cell 2</td>
                        <td>Cell 3</td>
                    </ExpandableTableRow>
                </tbody>
            </table>
        );

        expect(screen).not.toContain(contentToExpand);
    });

    test('row is expanded on click', () => {
        const contentToExpand = 'Hello World!';
        renderWithProviders(
            <table>
                <thead>
                    <tr>
                        <th>header 1</th>
                        <th>header 2</th>
                        <th>header 3</th>
                    </tr>
                </thead>
                <tbody>
                    <ExpandableTableRow contentToExpand={contentToExpand}>
                        <td>Cell 1</td>
                        <td>Cell 2</td>
                        <td>Cell 3</td>
                    </ExpandableTableRow>
                </tbody>
            </table>
        );

        userEvent.click(screen.getByText('Cell 1'));

        const colspan = screen.getByTestId('expanded-row-large-cell');
        const expanded = screen.getByText(contentToExpand);
        expect(colspan.getAttribute('colspan')).toEqual('3');
        expect(expanded).toBeInTheDocument();
    });

    test('expanded row is shrunk on click', () => {
        const contentToExpand = 'Hello World!';
        renderWithProviders(
            <table>
                <thead>
                    <tr>
                        <th>header 1</th>
                        <th>header 2</th>
                        <th>header 3</th>
                    </tr>
                </thead>
                <tbody>
                    <ExpandableTableRow contentToExpand={contentToExpand}>
                        <td>Cell 1</td>
                        <td>Cell 2</td>
                        <td>Cell 3</td>
                    </ExpandableTableRow>
                </tbody>
            </table>
        );

        userEvent.click(screen.getByText('Cell 1'));
        userEvent.click(screen.getByText('Cell 1'));

        const expanded = screen.queryByText(contentToExpand);
        expect(expanded).not.toBeInTheDocument();
    });
});
