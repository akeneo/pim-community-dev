import {act, renderHook} from '@testing-library/react-hooks';
import {usePersistentUser, User} from './usePersistentUser';
import {apiFetch} from '../api/apiFetch';
import {UnauthorizedError} from '../api';

const user: User = {email: 'burger@example.com'};

afterEach(() => {
    localStorage.clear();
});

jest.mock('../api/apiFetch');

test('it returns no user by default', () => {
    const {result} = renderHook(() => usePersistentUser());
    expect(result.current.user).toBeNull();
    expect(result.current.isInitialized).toBe(true);
    expect(localStorage.getItem('supplier-portal-contributor-account')).toBeNull();
});

test('it is able to load a user from local storage', async () => {
    apiFetch.mockResolvedValue({});

    localStorage.setItem('supplier-portal-contributor-account', JSON.stringify(user));
    const {result, waitForNextUpdate} = renderHook(() => usePersistentUser());
    await act(async () => {
        await waitForNextUpdate();
    });
    expect(result.current.isInitialized).toBe(true);
    expect(result.current.user).toStrictEqual(user);
});

test('it can persist the user in the local storage', async () => {
    expect(localStorage.getItem('supplier-portal-contributor-account')).toBeNull();
    const {result, waitFor} = renderHook(() => usePersistentUser());
    expect(result.current.user).toBeNull();
    result.current.updateUser(user);
    expect(result.current.user).toStrictEqual(user);

    await waitFor(() => {
        expect(JSON.parse(localStorage.getItem('supplier-portal-contributor-account'))).toStrictEqual(user);
    });
});

test('it can remove a logged out user from the local storage', async () => {
    apiFetch.mockRejectedValue(new UnauthorizedError());

    localStorage.setItem('supplier-portal-contributor-account', JSON.stringify(user));

    const {result, waitFor, waitForNextUpdate} = renderHook(() => usePersistentUser());

    await act(async () => {
        await waitForNextUpdate();
    });

    result.current.updateUser(null);
    expect(result.current.user).toBeNull();

    await waitFor(() => {
        expect(localStorage.getItem('supplier-portal-contributor-account')).toBeNull();
    });
});
