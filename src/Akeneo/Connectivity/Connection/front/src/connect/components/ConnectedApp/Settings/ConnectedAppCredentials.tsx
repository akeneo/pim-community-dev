import React, {FC} from 'react';
import {ConnectedApp} from '../../../../model/Apps/connected-app';
import {Button, KeyIcon, SectionTitle, UserIcon} from 'akeneo-design-system';
import {useTranslate} from '../../../../shared/translate';
import styled from 'styled-components';
import {CopiableCredential} from '../../../../settings/components/credentials/CopiableCredential';
import {Credential} from '../../../../settings/components/credentials/Credential';
import {useFetchCustomAppSecret} from '../../../hooks/use-fetch-custom-app-secret';
import {useHistory} from 'react-router';
import {useRouter} from '../../../../shared/router/use-router';
import {useSecurity} from '../../../../shared/security';

export const CredentialList = styled.div`
    display: grid;
    grid-template-columns: 44px 140px repeat(2, auto);
`;

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppCredentials: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const history = useHistory();
    const generateUrl = useRouter();
    const security = useSecurity();

    const {data: secret} = useFetchCustomAppSecret(connectedApp.id);

    const regenerateSecretUrl = `${generateUrl(
        'akeneo_connectivity_connection_connect_connected_apps_regenerate_secret',
        {
            connectionCode: connectedApp.connection_code,
        }
    )}`;

    const showRegenerateButton = security.isGranted('akeneo_connectivity_connection_manage_test_apps');

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.title')}
                </SectionTitle.Title>
            </SectionTitle>
            <CredentialList>
                <CopiableCredential
                    icon={<UserIcon></UserIcon>}
                    label={translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.client_id'
                    )}
                >
                    {connectedApp.id}
                </CopiableCredential>
                <Credential
                    icon={<KeyIcon />}
                    label={translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.client_secret'
                    )}
                    actions={
                        showRegenerateButton && (
                            <Button
                                ghost
                                level='secondary'
                                size='small'
                                onClick={() => history.push(regenerateSecretUrl)}
                            >
                                {translate(
                                    'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_button'
                                )}
                            </Button>
                        )
                    }
                >
                    {secret ?? ''}
                </Credential>
            </CredentialList>
        </>
    );
};
