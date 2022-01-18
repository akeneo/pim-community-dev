import {Authentication} from '@src/connect/components/AppWizard/steps/Authentication/Authentication';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import React from 'react';
import {renderWithProviders} from '../../../../../../test-utils';

jest.mock('@src/connect/components/AppWizard/steps/Authentication/UserAvatar', () => ({
    UserAvatar: () => <div>UserAvatar</div>,
}));
jest.mock('@src/connect/components/AppWizard/steps/Authentication/ConsentList', () => ({
    ConsentList: () => <div>ConsentList</div>,
}));

test('it renders correctly', async () => {
    renderWithProviders(<Authentication appName='MyApp' scopes={[]} />);

    await waitFor(() =>
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    ).toBeInTheDocument();
    expect(screen.queryByText('UserAvatar')).toBeInTheDocument();
    expect(screen.queryByText('ConsentList')).toBeInTheDocument();
});
