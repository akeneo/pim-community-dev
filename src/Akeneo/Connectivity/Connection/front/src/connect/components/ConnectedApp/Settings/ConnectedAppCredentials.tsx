import React, {FC} from 'react';
import {ConnectedApp} from '../../../../model/Apps/connected-app';
import {KeyIcon, SectionTitle, UserIcon} from 'akeneo-design-system';
import {useTranslate} from '../../../../shared/translate';
import styled from 'styled-components';
import {CopiableCredential} from '../../../../settings/components/credentials/CopiableCredential';
import {Credential} from '../../../../settings/components/credentials/Credential';
import {useFetchCustomAppSecret} from '../../../hooks/use-fetch-custom-app-secret';

export const CredentialList = styled.div`
    display: grid;
    grid-template-columns: 40px 140px repeat(2, auto);
`;

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppCredentials: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();

    const {data: secret} = useFetchCustomAppSecret(connectedApp.id);

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
                >
                    {secret ?? ''}
                </Credential>
            </CredentialList>
        </>
    );
};
