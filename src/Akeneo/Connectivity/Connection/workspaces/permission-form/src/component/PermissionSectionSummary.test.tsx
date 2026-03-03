import React, {FC} from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {LevelSummaryField, PermissionSectionSummary} from './PermissionSectionSummary';

jest.mock('../dependencies/translate', () => ({
    __esModule: true,
    default: (key: string) => key,
}));
const Icon: FC = () => <div>icon</div>;

test('it renders section without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <PermissionSectionSummary label={'any_label'}>
                <div>child</div>
            </PermissionSectionSummary>
        </ThemeProvider>
    );

    expect(screen.queryByText('any_label')).toBeInTheDocument();
    expect(screen.queryByText('child')).toBeInTheDocument();
});

test('it renders field without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <LevelSummaryField levelLabel={'any_label'} icon={<Icon />}>
                <div>child</div>
            </LevelSummaryField>
        </ThemeProvider>
    );

    expect(screen.queryByText('any_label')).toBeInTheDocument();
    expect(screen.queryByText('child')).toBeInTheDocument();
    expect(screen.queryByText('icon')).toBeInTheDocument();
});
