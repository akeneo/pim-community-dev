import React from 'react';
import {fireEvent, screen, waitFor} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {setLogger} from 'react-query';
import {useRegenerateCustomAppSecret} from '@src/connect/hooks/use-regenerate-custom-app-secret';

setLogger({
    log: () => null,
    warn: () => null,
    error: () => null, // explicit error generation triggers react query to log the error
});

test('it regenerates the custom app and returns a new secret', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_custom_apps_rest_regenerate_secret?customAppId=appId': {
            json: 'newCustomAppSecret',
            status: 200,
        },
    });

    const onSuccess = jest.fn();
    const CustomComponent = () => {
        const {data, mutate} = useRegenerateCustomAppSecret();
        return (
            <div>
                <h1>{data}</h1>
                <button onClick={() => mutate('appId', {onSuccess})}> mutate </button>
            </div>
        );
    };

    renderWithProviders(<CustomComponent />);

    await waitFor(() => screen.getByText('mutate'));

    fireEvent.click(screen.getByText('mutate'));

    await waitFor(() => expect(screen.queryByText('newCustomAppSecret')).toBeInTheDocument());

    expect(fetchMock).toHaveBeenCalledWith(
        'akeneo_connectivity_connection_custom_apps_rest_regenerate_secret?customAppId=appId',
        expect.objectContaining({
            method: 'POST',
        })
    );

    expect(onSuccess).toBeCalledWith('newCustomAppSecret', expect.anything(), undefined);

    done();
});

test('it returns an error', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_custom_apps_rest_regenerate_secret?customAppId=appId': {
            json: {},
            status: 500,
        },
    });

    const CustomComponent = () => {
        const {error, mutate, isError, isSuccess} = useRegenerateCustomAppSecret();
        return (
            <div>
                <h1>{isSuccess && 'Success'}</h1>
                <h1>{isError && 'Error occurred'}</h1>
                <h1>{error?.toString()}</h1>
                <button onClick={() => mutate('appId')}> mutate </button>
            </div>
        );
    };

    renderWithProviders(<CustomComponent />);

    await waitFor(() => screen.getByText('mutate'));

    fireEvent.click(screen.getByText('mutate'));

    await waitFor(() => expect(screen.queryByText('Error occurred')).toBeInTheDocument());

    expect(screen.queryByText('Success')).not.toBeInTheDocument();
    expect(screen.queryByText('Error: 500 Internal Server Error')).toBeInTheDocument();

    expect(fetchMock).toHaveBeenCalledWith(
        'akeneo_connectivity_connection_custom_apps_rest_regenerate_secret?customAppId=appId',
        expect.objectContaining({
            method: 'POST',
        })
    );

    done();
});
