import {Authentication} from '@src/connect/components/AppWizard/steps/Authentication/Authentication';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, waitFor} from '@testing-library/react';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import {ThemeProvider} from 'styled-components';

jest.mock('@src/connect/components/AppWizard/steps/Authentication/UserAvatar', () => ({
    UserAvatar: () => <div>UserAvatar</div>,
}));
jest.mock('@src/connect/components/AppWizard/steps/Authentication/ConsentList', () => ({
    ConsentList: () => <div>ConsentList</div>,
}));

test('it renders correctly', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Authentication appName='MyApp' scopes={[]} />
        </ThemeProvider>
    );

    await waitFor(() =>
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    ).toBeInTheDocument();
    expect(screen.queryByText('UserAvatar')).toBeInTheDocument();
    expect(screen.queryByText('ConsentList')).toBeInTheDocument();
});
