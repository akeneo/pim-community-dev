import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, waitForElement} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Authorizations} from '@src/connect/components/AppWizardWithSteps/Authorizations';

jest.mock('@src/connect/components/AppWizard/ScopeListContainer', () => ({
    ScopeListContainer: () => <div>ScopeListContainerComponent</div>,
}));
test('The authorizations step renders without error', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Authorizations appName={'MyApp'} scopeMessages={[]} />
        </ThemeProvider>
    );
    await waitForElement(() => screen.getByText('akeneo_connectivity.connection.connect.apps.title'));
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.title')).toBeInTheDocument();
    expect(screen.queryByText('ScopeListContainerComponent')).toBeInTheDocument();
});
