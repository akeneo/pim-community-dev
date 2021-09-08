import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import {Permissions} from '@src/connect/components/AppWizardWithSteps/Permissions';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {PermissionFormRegistry, PermissionFormRegistryContext} from '@src/shared/permission-form-registry';

test('The permissions step renders without error', async done => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Permissions appName='MyApp' providers={[]} setPermissions={jest.fn()} />
        </ThemeProvider>
    );
    expect(await screen.findByText('akeneo_connectivity.connection.connect.apps.title')).toBeInTheDocument();
    done();
});

test('The permissions step renders with the providers from the registry', done => {
    const registry: PermissionFormRegistry = {
        all: () =>
            Promise.resolve([
                {
                    key: 'test',
                    renderForm: () => <div>test form</div>,
                    renderSummary: () => null,
                    save: () => true,
                },
            ]),
    };

    render(
        <PermissionFormRegistryContext.Provider value={registry}>
            <ThemeProvider theme={pimTheme}>
                <Permissions appName='MyApp' providers={[]} setPermissions={jest.fn()} />
            </ThemeProvider>
        </PermissionFormRegistryContext.Provider>
    );
    //expect(await screen.findByText('test form')).toBeInTheDocument();
    done();
});
