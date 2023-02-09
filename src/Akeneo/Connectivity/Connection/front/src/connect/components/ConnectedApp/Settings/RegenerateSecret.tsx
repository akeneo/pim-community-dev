import React, {useState} from 'react';
import {Button, KeyIcon, Modal, SettingsIllustration, UserIcon} from 'akeneo-design-system';
import {useHistory, useParams} from 'react-router';
import {useTranslate} from '../../../../shared/translate';
import {useNotify} from '../../../../shared/notify';
import {useConnectedApp} from '../../../hooks/use-connected-app';
import {useRouter} from '../../../../shared/router/use-router';
import styled from 'styled-components';
import {CopiableCredential} from '../../../../settings/components/credentials/CopiableCredential';
import {Credential} from '../../../../settings/components/credentials/Credential';

const Description = styled.div`
    font-size: 17px;
    max-width: 460px;
`;

export const CredentialList = styled.div`
    display: grid;
    grid-template-columns: 44px 140px repeat(2, auto);
`;

type Props = {
};

export type Step = {
    name: 'confirm' | 'new_credentials';
};


export const RegenerateSecret = ({}: Props) => {
    const translate = useTranslate();
    const history = useHistory();
    const notify = useNotify();
    const generateUrl = useRouter();

    const {connectionCode} = useParams<{connectionCode: string}>();

    const {loading, error, payload: connectedApp} = useConnectedApp(connectionCode);

    const [step, setStep] = useState<Step>({name: 'confirm'});


    const handleRedirect = () => {
        history.push(`${generateUrl('akeneo_connectivity_connection_connect_connected_apps_edit', {
            connectionCode: connectionCode,
        })}`);
    };

    const handleClick = async () => {
        // const url = useRoute('akeneo_connectivity_custom_app_rest_regenerate_secret', {connection_code});
        // const result = await fetchResult<undefined, undefined>(url, {
        //     method: 'POST',
        // });
        //
        // if (isErr(result)) {
        //     notify(NotificationLevel.ERROR, translate('akeneo_connectivity.connection.regenerate_secret.flash.error'));
        // } else {
        //     notify(
        //         NotificationLevel.SUCCESS,
        //         translate('akeneo_connectivity.connection.regenerate_secret.flash.success')
        //     );
        // }
        //
        // handleRedirect();
        setStep({name:'new_credentials'});
    };

    return (
        <Modal onClose={handleRedirect} closeTitle={translate('pim_common.close')} illustration={<SettingsIllustration />}>
            { step.name == 'confirm' &&
                <>
                    <Modal.Title>{translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.title')}</Modal.Title>
                    <Description>{translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.description')}</Description>
                    <Modal.BottomButtons>
                        <Button level="tertiary" onClick={() => handleRedirect()}>
                            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.cancel_button')}
                        </Button>
                        <Button level="primary" onClick={() => handleClick()}>
                            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.regenerate_button')}
                        </Button>
                    </Modal.BottomButtons>
                </>
            }
            { step.name == 'new_credentials' &&
                <>
                    <Modal.SectionTitle color='brand'>{translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.title')}</Modal.SectionTitle>
                    <Modal.Title>New credentials</Modal.Title>
                    <CredentialList>
                        <CopiableCredential
                            icon={<UserIcon></UserIcon>}
                            label={translate(
                                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.client_id'
                            )}
                        >
                            0ad16a5e-19f2-4dbb-9779-469b62301e14
                        </CopiableCredential>
                        <CopiableCredential
                            icon={<KeyIcon />}
                            label={translate(
                                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.client_secret'
                            )}
                        >
                            ZGE5ZDM4OWY5OTkxODBjZTkwZTU5ZGRmNTQ2ZmFhYTRmNzg5OTVjNTVjYzc4NTJmZmVlNTgxYjgxZmFkMzMwNQ
                        </CopiableCredential>
                    </CredentialList>
                    <Modal.BottomButtons>
                        <Button level="primary" onClick={() => handleRedirect()}>
                            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.done_button')}
                        </Button>
                    </Modal.BottomButtons>
                </>
            }
        </Modal>
    );
};
