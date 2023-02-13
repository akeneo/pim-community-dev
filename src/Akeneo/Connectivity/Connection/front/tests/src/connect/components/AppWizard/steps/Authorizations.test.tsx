import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, waitFor} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Authorizations} from '@src/connect/components/AppWizard/steps/Authorizations';

jest.mock('@src/connect/components/AppWizard/ScopeListContainer', () => ({
    ScopeListContainer: () => <div>ScopeListContainerComponent</div>,
}));
jest.mock('@src/connect/components/AppWizard/steps/Authentication/ConsentCheckbox', () => ({
    ConsentCheckbox: () => <div>ConsentCheckbox</div>,
}));
jest.mock('@src/connect/components/AppWizard/steps/Authorization/CertificationConsentCheckbox', () => ({
    CertificationConsentCheckbox: () => <div>CertificationConsentCheckbox</div>,
}));

test('The authorizations step without certification consent renders without error', async () => {
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
                displayCheckboxConsent={true}
            />
        </ThemeProvider>
    );
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.apps.title'));
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.title')).toBeInTheDocument();
    expect(screen.queryByText('ScopeListContainerComponent')).toBeInTheDocument();
    expect(screen.queryByText('ConsentCheckbox')).toBeInTheDocument();
    expect(screen.queryByText('CertificationConsentCheckbox')).not.toBeInTheDocument();
});

test('The authorizations step with certification consent renders without error', async () => {
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
                displayCertificationConsent={true}
                displayCheckboxConsent={true}
            />
        </ThemeProvider>
    );
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.apps.title'));
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.title')).toBeInTheDocument();
    expect(screen.queryByText('ScopeListContainerComponent')).toBeInTheDocument();
    expect(screen.queryByText('ConsentCheckbox')).toBeInTheDocument();
    expect(screen.queryByText('CertificationConsentCheckbox')).toBeInTheDocument();
});
