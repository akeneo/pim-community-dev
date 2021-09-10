import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import {Permissions} from '@src/connect/components/AppWizardWithSteps/Permissions';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

test('The permissions step renders without error', done => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Permissions appName='MyApp' providers={[]} setPermissions={jest.fn()} permissions={{}}/>
        </ThemeProvider>
    );
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.title')).toBeInTheDocument();
    done();
});

test('The permissions step renders with the providers from the registry', done => {
    const providers = [
        {
            key: 'test',
            renderForm: (_onChange: any, initialState: any) => <div>test form {initialState.print}</div>,
            renderSummary: () => null,
            save: () => true,
        },
    ];

    render(
        <ThemeProvider theme={pimTheme}>
            <Permissions
                appName='MyApp'
                providers={providers}
                setPermissions={jest.fn()}
                permissions={{test: {print: 'hello world!'}}}
            />
        </ThemeProvider>
    );
    expect(screen.queryByText('test form hello world!')).toBeInTheDocument();
    done();
});
