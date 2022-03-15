import {useFeatureFlags} from '@src/shared/feature-flags';
import {renderHook} from '@testing-library/react-hooks';
import {useDeveloperMode} from '@src/connect/hooks/use-developer-mode';

jest.mock('@src/shared/feature-flags/use-feature-flags');

beforeEach(() => {
    jest.clearAllMocks();
});

type Feature = 'app_developer_mode';

const tests = [
    {
        features: {
            app_developer_mode: true,
        },
        result: true,
    },
    {
        features: {
            app_developer_mode: false,
        },
        result: false,
    },
];

test.each(tests)('It check if all conditions are met for the Developer mode', data => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: Feature) => data.features[feature] ?? false,
    }));

    const {result} = renderHook(() => useDeveloperMode());
    expect(result.current).toBe(data.result);
});
