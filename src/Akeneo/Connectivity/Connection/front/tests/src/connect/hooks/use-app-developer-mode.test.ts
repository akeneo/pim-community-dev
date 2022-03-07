import {useFeatureFlags} from '@src/shared/feature-flags';
import {useSecurity} from '@src/shared/security';
import {renderHook} from '@testing-library/react-hooks';
import {useAppDeveloperMode} from '@src/connect/hooks/use-app-developer-mode';

jest.mock('@src/shared/feature-flags/use-feature-flags');
jest.mock('@src/shared/security/use-security');

beforeEach(() => {
    jest.clearAllMocks();
});

type Feature = 'app_developer_mode' | 'marketplace_activate';
type Acl = 'akeneo_connectivity_connection_manage_test_apps';

const tests = [
    {
        features: {
            app_developer_mode: true,
            marketplace_activate: true,
        },
        acls: {
            akeneo_connectivity_connection_manage_test_apps: true,
        },
        result: true,
    },
    {
        features: {
            app_developer_mode: false,
            marketplace_activate: true,
        },
        acls: {
            akeneo_connectivity_connection_manage_test_apps: true,
        },
        result: false,
    },
    {
        features: {
            app_developer_mode: true,
            marketplace_activate: false,
        },
        acls: {
            akeneo_connectivity_connection_manage_test_apps: true,
        },
        result: false,
    },
    {
        features: {
            app_developer_mode: true,
            marketplace_activate: true,
        },
        acls: {
            akeneo_connectivity_connection_manage_test_apps: false,
        },
        result: false,
    },
];

test.each(tests)('It check if all conditions are met for the App Developer mode', data => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: Feature) => data.features[feature] ?? false,
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: Acl) => data.acls[acl] ?? false,
    }));

    const {result} = renderHook(() => useAppDeveloperMode());
    expect(result.current).toBe(data.result);
});
