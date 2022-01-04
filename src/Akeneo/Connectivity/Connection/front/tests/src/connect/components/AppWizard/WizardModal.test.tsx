import {WizardModal} from '@src/connect/components/AppWizard/WizardModal';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import {ThemeProvider} from 'styled-components';

test('it renders a single step wizard modal', async () => {
    const handleClose = jest.fn();
    const handleConfirm = jest.fn();
    const renderChildren = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <WizardModal
                appLogo='MyAppLogo'
                appName='MyApp'
                onClose={handleClose}
                onConfirm={handleConfirm}
                steps={[
                    {
                        name: 'authentication',
                        action: 'confirm',
                    },
                ]}
            >
                {renderChildren}
            </WizardModal>
        </ThemeProvider>
    );

    expect(renderChildren).toBeCalledWith({
        name: 'authentication',
        action: 'confirm',
    });

    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.progress.authentication')
    ).not.toBeInTheDocument();
});

test('it renders a multi steps wizard modal', async () => {
    const handleClose = jest.fn();
    const handleConfirm = jest.fn();
    const renderChildren = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <WizardModal
                appLogo='MyAppLogo'
                appName='MyApp'
                onClose={handleClose}
                onConfirm={handleConfirm}
                steps={[
                    {
                        name: 'authentication',
                        action: 'next',
                    },
                    {
                        name: 'authorizations',
                        action: 'confirm',
                    },
                ]}
            >
                {renderChildren}
            </WizardModal>
        </ThemeProvider>
    );

    expect(renderChildren).toBeCalledWith({
        name: 'authentication',
        action: 'next',
    });

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.progress.authentication')
    ).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')
    ).not.toBeInTheDocument();
});
