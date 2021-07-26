import {createContext} from 'react';
import {FeatureFlags} from './feature-flags.interface';

export const FeatureFlagsContext = createContext<FeatureFlags>({
    isEnabled: () => {
        return false;
    },
});
