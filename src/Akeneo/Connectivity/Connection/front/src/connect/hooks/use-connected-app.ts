import {ConnectedApp} from '../../model/Apps/connected-app';
import {useEffect, useState} from 'react';
import {useFetchConnectedApp} from './use-fetch-connected-app';
import {useFeatureFlags} from '../../shared/feature-flags';
import {HttpError} from '../../model/http-error.enum';

type Result = {
    loading: boolean;
    error: HttpError | null;
    payload: ConnectedApp | null;
};

export const useConnectedApp = (connectionCode: string): Result => {
    const featureFlag = useFeatureFlags();
    const fetchConnectedApp = useFetchConnectedApp(connectionCode);
    const [result, setResult] = useState<Result>({
        loading: true,
        error: null,
        payload: null,
    });

    useEffect(() => {
        if (!featureFlag.isEnabled('marketplace_activate')) {
            setResult({
                loading: false,
                error: HttpError.Forbidden,
                payload: null,
            });
            return;
        }

        (async () => {
            try {
                const connectedApp = await fetchConnectedApp();
                setResult({
                    loading: false,
                    error: null,
                    payload: connectedApp,
                });
            } catch (e) {
                setResult({
                    loading: false,
                    error: e === '403 Forbidden' ? HttpError.Forbidden : HttpError.NotFound,
                    payload: null,
                });
            }
        })();
    }, [fetchConnectedApp]);

    return result;
};
