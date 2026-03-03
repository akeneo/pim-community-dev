import React, {useCallback, useState} from 'react';
import {Modal, SettingsIllustration} from 'akeneo-design-system';
import {useHistory, useParams} from 'react-router';
import {useTranslate} from '../../shared/translate';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRouter} from '../../shared/router/use-router';
import {useRegenerateCustomAppSecret} from '../hooks/use-regenerate-custom-app-secret';
import {useConnectedApp} from '../hooks/use-connected-app';
import {RegenerateSecretConfirm} from '../components/CustomApps/RegenerateSecret/RegenerateSecretConfirm';
import {RegenerateSecretNewCredentials} from '../components/CustomApps/RegenerateSecret/RegenerateSecretNewCredentials';

type Step = 'confirm' | 'new_credentials';

export const RegenerateSecretPage = () => {
    const translate = useTranslate();
    const history = useHistory();
    const notify = useNotify();
    const generateUrl = useRouter();
    const {connectionCode} = useParams<{connectionCode: string}>();
    const [step, setStep] = useState<Step>('confirm');
    const [secret, setSecret] = useState<string | null>(null);

    const {loading: connectedAppLoading, payload: connectedApp} = useConnectedApp(connectionCode);

    const mutationRegenerateSecret = useRegenerateCustomAppSecret();

    const handleRedirect = () => {
        history.push(
            `${generateUrl('akeneo_connectivity_connection_connect_connected_apps_edit', {
                connectionCode: connectionCode,
            })}`
        );
    };

    const handleRegenerate = useCallback(() => {
        if (connectedApp) {
            mutationRegenerateSecret.mutate(connectedApp.id, {
                onSuccess: secret => {
                    setSecret(secret);
                    setStep('new_credentials');
                },
                onError: () => {
                    notifyError();
                },
            });
        } else {
            notifyError();
        }
    }, [connectedApp, setSecret]);

    const notifyError = function () {
        notify(
            NotificationLevel.ERROR,
            translate(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.flash.error'
            )
        );
    };

    if (connectedAppLoading || !connectedApp) {
        return null;
    }

    return (
        <Modal
            onClose={handleRedirect}
            closeTitle={translate('pim_common.close')}
            illustration={<SettingsIllustration />}
        >
            {step === 'confirm' && (
                <RegenerateSecretConfirm
                    handleRedirect={handleRedirect}
                    handleRegenerate={handleRegenerate}
                    buttonDisabled={mutationRegenerateSecret.isLoading}
                />
            )}
            {step === 'new_credentials' && (
                <RegenerateSecretNewCredentials
                    handleRedirect={handleRedirect}
                    clientId={connectedApp.id}
                    clientSecret={secret}
                />
            )}
        </Modal>
    );
};
