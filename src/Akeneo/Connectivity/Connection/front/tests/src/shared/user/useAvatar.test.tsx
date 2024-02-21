import {useAvatar, UserContext} from '@src/shared/user';
import {renderHook} from '@testing-library/react-hooks';
import React, {FC} from 'react';

test('it returns the user avatar data', () => {
    const user = {
        get: jest.fn((property: string): any => {
            switch (property) {
                case 'avatar':
                    return {filePath: 'path_to_a_file'};
                case 'first_name':
                    return 'John';
                case 'last_name':
                    return 'Doe';
            }
        }),
        set: jest.fn(),
        refresh: jest.fn(),
    };

    const wrapper: FC = ({children}) => <UserContext.Provider value={user}>{children}</UserContext.Provider>;
    const {result} = renderHook(() => useAvatar(), {wrapper});

    const expectedData = {
        imageUrl: 'pim_enrich_media_show?filename=path_to_a_file&filter=thumbnail_small',
        firstName: 'John',
        lastName: 'Doe',
    };
    expect(result.current).toStrictEqual(expectedData);
});

test('it fallbacks to the default image if the user has no avatar', () => {
    const user = {
        get: jest.fn((property: string): any => {
            switch (property) {
                case 'avatar':
                    return {filePath: null};
                case 'first_name':
                    return 'John';
                case 'last_name':
                    return 'Doe';
            }
        }),
        set: jest.fn(),
        refresh: jest.fn(),
    };

    const wrapper: FC = ({children}) => <UserContext.Provider value={user}>{children}</UserContext.Provider>;
    const {result} = renderHook(() => useAvatar(), {wrapper});

    const expectedData = {
        imageUrl: 'bundles/pimui/images/info-user.png',
        firstName: 'John',
        lastName: 'Doe',
    };
    expect(result.current).toStrictEqual(expectedData);
});
