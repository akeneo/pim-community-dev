import {useFeatureFlags} from '../../shared/feature-flags';

export const useDeveloperMode: () => boolean = () => {
    const featureFlag = useFeatureFlags();

    return featureFlag.isEnabled('app_developer_mode');
};
