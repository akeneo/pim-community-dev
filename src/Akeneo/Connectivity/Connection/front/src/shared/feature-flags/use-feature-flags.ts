import {useContext} from 'react';
import {FeatureFlagsContext} from './feature-flags-context';

export const useFeatureFlags = () => useContext(FeatureFlagsContext);
