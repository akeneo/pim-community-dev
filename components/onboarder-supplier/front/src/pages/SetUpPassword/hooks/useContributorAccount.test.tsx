import {useContributorAccount} from './useContributorAccount';
import {renderHookWithProviders} from '../../../tests';
import {act} from '@testing-library/react-hooks';
import * as toasterHook from '../../../utils/toaster';
import reactRouterDom from 'react-router-dom';
import * as passwordSaver from '../api/savePassword';
import {routes} from '../../routes';
import {BadRequestError} from '../../../api/BadRequestError';

jest.mock('react-router-dom');
jest.mock('../../../utils/toaster');
jest.mock('../api/savePassword');

const contributorAccount = {
    id: '132456',
    email: 'contrib1@example.com',
    isAccessTokenValid: true,
};

jest.mock('../api/fetchContributorAccount', () => ({
    fetchContributorAccount: () => {
        return new Promise(resolve => resolve(contributorAccount));
    },
}));

test('it fetches the contributor account using the access token', async () => {
    reactRouterDom.useHistory = jest.fn().mockReturnValue({push: jest.fn()});

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useContributorAccount('burger'));

    await act(async () => {
        await waitForNextUpdate();
    });

    expect(result.current.loadingError).toBeFalsy();
    expect(result.current.contributorAccount).toStrictEqual(contributorAccount);
});

test('it can set up a password for a contributor account', async () => {
    const notifyMock = jest.fn();
    toasterHook.useToaster = jest.fn().mockReturnValue(notifyMock);

    const historyPushMock = jest.fn();
    reactRouterDom.useHistory = jest.fn().mockReturnValue({push: historyPushMock});

    const savePasswordMock = jest.fn().mockImplementationOnce(() =>
        Promise.resolve({
            json: () => Promise.resolve({}),
            status: 200,
        })
    );
    passwordSaver.savePassword = savePasswordMock;

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useContributorAccount('burger'));

    await act(async () => {
        await waitForNextUpdate();
    });

    await act(async () => {
        await result.current.submitPassword('newpassword');
    });

    expect(savePasswordMock).toHaveBeenNthCalledWith(1, {
        contributorAccountIdentifier: '132456',
        plainTextPassword: 'newpassword',
    });
    expect(notifyMock).toHaveBeenNthCalledWith(
        1,
        'Your account has been successfully activated, you can now log into the application.',
        'success'
    );
    expect(historyPushMock).toHaveBeenNthCalledWith(1, routes.login);

    expect(result.current.passwordHasErrors).toBe(false);
});

test('it returns an error if the password is invalid', async () => {
    const notifyMock = jest.fn();
    toasterHook.useToaster = jest.fn().mockReturnValue(notifyMock);

    const historyPushMock = jest.fn();
    reactRouterDom.useHistory = jest.fn().mockReturnValue({push: historyPushMock});

    const savePasswordMock = jest.fn().mockRejectedValue(new BadRequestError(Promise.resolve()));
    passwordSaver.savePassword = savePasswordMock;

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useContributorAccount('burger'));

    await act(async () => {
        await waitForNextUpdate();
    });

    await act(async () => {
        await result.current.submitPassword('newpassword');
    });

    expect(savePasswordMock).toHaveBeenNthCalledWith(1, {
        contributorAccountIdentifier: '132456',
        plainTextPassword: 'newpassword',
    });
    expect(notifyMock).not.toHaveBeenCalled();
    expect(historyPushMock).not.toHaveBeenCalled();

    expect(result.current.passwordHasErrors).toBe(true);
});
