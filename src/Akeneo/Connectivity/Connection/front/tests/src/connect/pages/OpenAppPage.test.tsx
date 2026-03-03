import React from 'react';
import {mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {OpenAppPage} from '@src/connect/pages/OpenAppPage';
import {waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';

/*eslint-disable */
declare global {
    namespace NodeJS {
        interface Global {
            window: any;
        }
    }
}
/*eslint-enable */

beforeEach(() => {
    fetchMock.resetMocks();
    jest.clearAllMocks();

    delete global.window.location;
    global.window = Object.create(window);
    global.window.location = {
        replace: jest.fn(),
    };
});

jest.mock('react-router-dom', () => ({
    ...jest.requireActual('react-router-dom'),
    useParams: jest.fn().mockReturnValue({connectionCode: 'some_connection_code'}),
}));

test('Page that redirects to the open app url', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_open_app_url?connectionCode=some_connection_code': {
            json: {
                url: 'http://app.example.com/open/app/url',
            },
        },
    });

    renderWithProviders(<OpenAppPage />);

    await waitFor(() => expect(window.location.replace).toHaveBeenCalledWith('http://app.example.com/open/app/url'));

    done();
});

test('Page notifies user of an issue on error during url fetch', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_open_app_url?connectionCode=some_connection_code': {
            reject: true,
            json: '',
        },
    });

    const notify = jest.fn();

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <OpenAppPage />
        </NotifyContext.Provider>
    );

    await waitFor(() =>
        expect(notify).toHaveBeenCalledWith(
            NotificationLevel.ERROR,
            'akeneo_connectivity.connection.connect.connected_apps.open.flash.error'
        )
    );

    done();
});
