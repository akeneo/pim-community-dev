import React from 'react';
import {act, fireEvent, render} from '@testing-library/react';
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";
import {AkeneoThemeProvider} from "../../../../../src/theme";
import {BreadcrumbItem} from "../../../../../src/components";

describe('Breadcrumb', () => {
    const renderWithContext = (item: any, props: {onClick?: () => void}) => {
        return render(
            <DependenciesProvider>
                <AkeneoThemeProvider>
                    <BreadcrumbItem {...props}>{item}</BreadcrumbItem>
                </AkeneoThemeProvider>
            </DependenciesProvider>
        );
    };

    test('it shows simple item', () => {
        const item = 'DUMMY_ITEM';

        const {queryByText} = renderWithContext(item, {});
        expect(queryByText(item)).not.toBeNull();
    });

    test('it shows clickable item', () => {
        const item = 'DUMMY_ITEM';
        const clickAction = jest.fn();

        const {queryByText, getByText} = renderWithContext(item, {onClick: clickAction});
        expect(queryByText(item)).not.toBeNull();

        act(() => {
            fireEvent.click(getByText(item));
        });

        expect(clickAction).toBeCalled();

    });
});
