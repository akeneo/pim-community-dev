import React, {ReactElement} from 'react';
import {render} from '@testing-library/react';
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";
import {AkeneoThemeProvider} from "../../../../src/theme";
import {PageHeader} from "../../../../src/components";

describe('PageHeader', () => {

    const renderWithContext = (title: any, showPlaceholder: boolean, buttons: ReactElement[] = [], imageSrc?: string) => {
        return render(
            <DependenciesProvider>
                <AkeneoThemeProvider>
                    <PageHeader showPlaceholder={showPlaceholder} buttons={buttons} imageSrc={imageSrc}>
                        {title}
                    </PageHeader>
                </AkeneoThemeProvider>
            </DependenciesProvider>
        );
    };

    test('it hides title when placeholder is shown', () => {
        const title = 'DUMMY_TITLE';

        const {queryByText} = renderWithContext(title, true);
        expect(queryByText(title)).toBeNull();
    });
    test('it shows title when placeholder is hidden', () => {
        const title = 'DUMMY_TITLE';

        const {queryByText} = renderWithContext(title, false);
        expect(queryByText(title)).not.toBeNull();
    });
    test('it shows actions', () => {
        const buttons = [
            <button>DUMMY_BUTTON_1</button>,
            <button>DUMMY_BUTTON_2</button>,
        ];
        const {queryByText} = renderWithContext('DUMMY_TITLE', false, buttons);
        expect(queryByText('DUMMY_BUTTON_1')).not.toBeNull();
        expect(queryByText('DUMMY_BUTTON_2')).not.toBeNull();
    });
    test('it shows image', () => {
        const title = 'DUMMY_TITLE';
        const imageSrc = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='
        const {findByAltText} = renderWithContext(title, false, [], imageSrc);

        expect(findByAltText(title)).not.toBeNull();
    });
});
