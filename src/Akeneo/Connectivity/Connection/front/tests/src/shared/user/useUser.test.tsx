import {UserContext, useUser} from '@src/shared/user';
import {renderHook} from '@testing-library/react-hooks';
import React, {FC} from 'react';

test('it returns the user locale and time zone', () => {
    const user = {
        get: jest.fn(),
        set: jest.fn(),
    };
    user.get.mockReturnValueOnce('fr_FR');
    user.get.mockReturnValueOnce('Europe/Paris');

    const wrapper: FC = ({children}) => <UserContext.Provider value={user}>{children}</UserContext.Provider>;

    const {result} = renderHook(() => useUser(), {wrapper});

    expect(user.get).toBeCalledWith('uiLocale');
    expect(result.current.locale).toBe('fr-FR');

    expect(user.get).toBeCalledWith('timezone');
    expect(result.current.timeZone).toBe('Europe/Paris');
});
