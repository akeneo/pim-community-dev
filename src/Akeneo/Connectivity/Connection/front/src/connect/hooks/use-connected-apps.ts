import {useEffect, useState} from 'react';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {ConnectedApp} from '../../model/Apps/connected-app';
import {useFeatureFlags} from '../../shared/feature-flags';
import {useFetchConnectedApps} from './use-fetch-connected-apps';
import {useFetchApps} from './use-fetch-apps';
import {useTranslate} from '../../shared/translate';
import {useFetchCustomApps} from './use-fetch-custom-apps';
import {App} from '../../model/app';
import {useTriggerConnectedAppRefresh} from './use-trigger-connected-app-refresh';

const hashAppSubset = ({name, logo, author, categories, certified, partner}: ConnectedApp | App): string => {
    return JSON.stringify({
        name,
        logo,
        author,
        categories,
        certified,
        partner,
    });
};

const isAppSubsetIdentical = (app: App, connectedApp: ConnectedApp): boolean => {
    return hashAppSubset(app) === hashAppSubset(connectedApp);
};

export const useConnectedApps = (): ConnectedApp[] | null | false => {
    const featureFlag = useFeatureFlags();
    const notify = useNotify();
    const translate = useTranslate();
    const fetchConnectedApps = useFetchConnectedApps();
    const fetchApps = useFetchApps();
    const fetchTestApps = useFetchCustomApps();
    const triggerConnectedAppRefresh = useTriggerConnectedAppRefresh();
    const [connectedApps, setConnectedApps] = useState<ConnectedApp[] | null | false>(null);

    useEffect(() => {
        let mounted = true;

        if (!featureFlag.isEnabled('marketplace_activate')) {
            setConnectedApps([]);
            return;
        }

        (async () => {
            let connectedApps: ConnectedApp[] | null | false;

            try {
                connectedApps = await fetchConnectedApps();
                mounted && setConnectedApps(connectedApps);
            } catch (e) {
                mounted && setConnectedApps(false);
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.connect.connected_apps.list.flash.error')
                );
                return;
            }

            if (!connectedApps || connectedApps.length === 0) {
                return;
            }

            try {
                const apps = await fetchApps();
                const testApps = await fetchTestApps();

                setConnectedApps(state => {
                    if (state === null || state === false) {
                        return state;
                    }

                    return state.map(connectedApp => {
                        const app =
                            apps.apps.find(app => app.id === connectedApp.id) ||
                            testApps.apps.find(app => app.id === connectedApp.id);

                        return {
                            ...connectedApp,
                            activate_url: app?.activate_url || undefined,
                            is_loaded: true,
                            is_listed_on_the_appstore: false === connectedApp.is_custom_app && undefined !== app,
                        };
                    });
                });

                // trigger a refresh when there is an inconsistency between the data in a connected app and the data
                // in the app store
                connectedApps.forEach(connectedApp => {
                    const app = apps.apps.find(app => app.id === connectedApp.id);

                    if (undefined !== app && !isAppSubsetIdentical(app, connectedApp)) {
                        triggerConnectedAppRefresh(connectedApp.connection_code);
                    }
                });
            } catch (e) {
                return;
            }
        })();

        return () => {
            mounted = false;
        };
    }, [fetchConnectedApps]);

    return connectedApps;
};
