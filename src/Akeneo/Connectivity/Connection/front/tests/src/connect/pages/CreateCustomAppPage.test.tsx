import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../test-utils';
import {CreateCustomAppPage} from '@src/connect/pages/CreateCustomAppPage';
import {CreateTestAppForm} from '@src/connect/components/TestApp/CreateTestAppForm';
import {CreateTestAppCredentials} from '@src/connect/components/TestApp/CreateTestAppCredentials';
import {TestAppCredentials} from '@src/model/Apps/test-app-credentials';
import userEvent from '@testing-library/user-event';
import {act} from '@testing-library/react-hooks';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

type CreateTestAppFormProps = {
    onCancel: () => void;
    setCredentials: (credentials: TestAppCredentials) => void;
};

jest.mock('@src/connect/components/TestApp/CreateTestAppForm', () => ({
    ...jest.requireActual('@src/connect/components/TestApp/CreateTestAppForm'),
    CreateTestAppForm: jest.fn(({onCancel, setCredentials}: CreateTestAppFormProps) => {
        const handleClick = () => {
            setCredentials({
                clientId: 'clientId',
                clientSecret: 'clientSecret',
            });
        };

        return (
            <div data-testid='submit-form' onClick={handleClick}>
                create TestApp form
            </div>
        );
    }),
}));

jest.mock('@src/connect/components/TestApp/CreateTestAppCredentials', () => ({
    ...jest.requireActual('@src/connect/components/TestApp/CreateTestAppCredentials'),
    CreateTestAppCredentials: jest.fn(({credentials}) => null),
}));

test('it renders the form without credentials and display them when form is submitted', () => {
    renderWithProviders(<CreateCustomAppPage />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.subtitle')
    ).toBeInTheDocument();

    expect(CreateTestAppForm).toHaveBeenCalled();
    expect(CreateTestAppCredentials).not.toHaveBeenCalled();

    act(() => {
        userEvent.click(screen.getByTestId('submit-form'));
    });

    expect(CreateTestAppCredentials).toHaveBeenCalledWith(
        expect.objectContaining({
            credentials: {
                clientId: 'clientId',
                clientSecret: 'clientSecret',
            },
        }),
        {}
    );
});
