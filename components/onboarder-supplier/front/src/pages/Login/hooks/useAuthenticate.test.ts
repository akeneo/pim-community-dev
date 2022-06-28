import {renderHookWithProviders} from '../../../tests';
import {act} from '@testing-library/react-hooks';
import reactRouterDom from 'react-router-dom';
import {useAuthenticate} from './useAuthenticate';
import {routes} from '../../routes';
import * as authenticateAPI from '../api/authenticate';
import {BadRequestError} from '../../../api/BadRequestError';

jest.mock('react-router-dom');
jest.mock('../api/authenticate');

test('it successfully authenticates a contributor', async () => {
    const historyPushMock = jest.fn();
    reactRouterDom.useHistory = jest.fn().mockReturnValue({push: historyPushMock});

    const authenticateMock = jest.fn().mockImplementationOnce(() =>
        Promise.resolve({
            json: () => Promise.resolve({}),
            status: 200,
        })
    );
    authenticateAPI.authenticate = authenticateMock;

    const {result} = renderHookWithProviders(() => useAuthenticate());

    await act(async () => {
        await result.current.login('mylogin', 'mypassword');
    });

    expect(authenticateMock).toHaveBeenNthCalledWith(1, {
        email: 'mylogin',
        password: 'mypassword',
    });
    expect(historyPushMock).toHaveBeenNthCalledWith(1, routes.filesDropping);
});

test('it fails to authenticate a contributor', async () => {
    const historyPushMock = jest.fn();
    reactRouterDom.useHistory = jest.fn().mockReturnValue({push: historyPushMock});

    const authenticateMock = jest.fn().mockRejectedValue(new BadRequestError(Promise.resolve()));
    authenticateAPI.authenticate = authenticateMock;

    const {result} = renderHookWithProviders(() => useAuthenticate());

    await act(async () => {
        await result.current.login('mylogin', 'mypassword');
    });

    expect(authenticateMock).toHaveBeenNthCalledWith(1, {
        email: 'mylogin',
        password: 'mypassword',
    });
    expect(historyPushMock).not.toHaveBeenCalled();
});
