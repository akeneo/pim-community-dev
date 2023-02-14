import React from 'react';
import {useCreateCustomApp} from '@src/connect/hooks/use-create-custom-app';
import {fireEvent, screen, waitFor} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {setLogger} from 'react-query';

setLogger({
    log: () => null,
    warn: () => null,
    error: () => null, // explicit error generation triggers react query to log the error
});

test('it creates the test app and returns credentials', async done => {
    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_create: {
            json: {
                clientId: 'testClientId',
                clientSecret: 'testSecret',
            },
            status: 200,
        },
    });

    const onSuccess = jest.fn();
    const TestComponent = () => {
        const {data, mutate} = useCreateCustomApp();
        return (
            <div>
                <h1>{data?.clientId}</h1>
                <h1>{data?.clientSecret}</h1>
                <button
                    onClick={() => {
                        mutate(
                            {
                                name: 'Custom app bynder',
                                activateUrl: 'http://any_url.test',
                                callbackUrl: 'http://activate.test',
                            },
                            {onSuccess}
                        );
                    }}
                >
                    mutate
                </button>
            </div>
        );
    };

    renderWithProviders(<TestComponent />);

    await waitFor(() => screen.getByText('mutate'));

    fireEvent.click(screen.getByText('mutate'));

    await waitFor(() => screen.getByText('testClientId'));

    expect(screen.getByText('testClientId')).toBeInTheDocument();
    expect(screen.getByText('testSecret')).toBeInTheDocument();

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_custom_apps_rest_create', {
        body: '{"name":"Custom app bynder","activateUrl":"http://any_url.test","callbackUrl":"http://activate.test"}',
        headers: {
            'Content-type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
    });

    expect(onSuccess).toBeCalledWith(
        {
            clientId: 'testClientId',
            clientSecret: 'testSecret',
        },
        expect.anything(),
        undefined
    );

    done();
});

test('it returns errors when fields are not valid', async done => {
    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_create: {
            json: {
                errors: [
                    {propertyPath: 'name', message: 'name_error'},
                    {propertyPath: 'activateUrl', message: 'activate_url_error'},
                    {propertyPath: 'callbackUrl', message: 'callback_url_error'},
                ],
            },
            status: 422,
        },
    });

    const TestComponent = () => {
        const {error, mutate, isError, isSuccess} = useCreateCustomApp();
        return (
            <div>
                <h1>{isSuccess && 'Success'}</h1>
                <h1>{isError && 'Error occurred'}</h1>
                <h1>{error?.name}</h1>
                <h1>{error?.callbackUrl}</h1>
                <h1>{error?.activateUrl}</h1>
                <button
                    onClick={() => {
                        mutate({
                            name: 'nt',
                            activateUrl: 'bad_url',
                            callbackUrl: 'bad_url',
                        });
                    }}
                >
                    mutate
                </button>
            </div>
        );
    };

    renderWithProviders(<TestComponent />);

    await waitFor(() => screen.getByText('mutate'));

    fireEvent.click(screen.getByText('mutate'));

    await waitFor(() => screen.getByText('Error occurred'));

    expect(screen.getByText('Error occurred')).toBeInTheDocument();
    expect(screen.queryByText('Success')).not.toBeInTheDocument();
    expect(screen.getByText('name_error')).toBeInTheDocument();
    expect(screen.getByText('activate_url_error')).toBeInTheDocument();
    expect(screen.getByText('callback_url_error')).toBeInTheDocument();

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_custom_apps_rest_create', {
        body: '{"name":"nt","activateUrl":"bad_url","callbackUrl":"bad_url"}',
        headers: {
            'Content-type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
    });
    done();
});
