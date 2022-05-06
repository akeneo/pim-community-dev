import {useSecurity} from '../../shared/security';
import {useFeatureFlags} from '../../shared/feature-flags';

export const useAppDeveloperMode: () => boolean = () => {
    const security = useSecurity();
    const featureFlag = useFeatureFlags();

    return (
        featureFlag.isEnabled('marketplace_activate') &&
        featureFlag.isEnabled('app_developer_mode') &&
        security.isGranted('akeneo_connectivity_connection_manage_test_apps')
    );
};
