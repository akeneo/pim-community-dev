import {ConnectedApp} from '../../model/Apps/connected-app';
import {useSecurity} from '../../shared/security';

export const useConnectedAppManageAcl: (app: ConnectedApp | null | false) => boolean = (app) => {
    const security = useSecurity();

    if (app === null || app === false) {
        return true;
    }

    return (app.is_test_app && security.isGranted('akeneo_connectivity_connection_manage_test_apps'))
        || (!app.is_test_app && security.isGranted('akeneo_connectivity_connection_manage_apps'));
};
