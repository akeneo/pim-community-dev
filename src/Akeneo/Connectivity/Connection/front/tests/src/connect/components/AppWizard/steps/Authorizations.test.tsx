import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, waitFor} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Authorizations} from '@src/connect/components/AppWizard/steps/Authorizations';

jest.mock('@src/connect/components/AppWizard/ScopeListContainer', () => ({
    ScopeListContainer: () => <div>ScopeListContainerComponent</div>,
}));
test('The authorizations step renders without error', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Authorizations
                appName={'MyApp'}
                scopeMessages={[]}
                appUrl={''}
                scopesConsentGiven={false}
                setScopesConsent={() => null}
                certificationConsentGiven={false}
                setCertificationConsent={() => null}
                displayCertificationConsent={false}
            />
        </ThemeProvider>
    );
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.apps.title'));
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.title')).toBeInTheDocument();
    expect(screen.queryByText('ScopeListContainerComponent')).toBeInTheDocument();
});
