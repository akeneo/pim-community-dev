import React from 'react';
import {Button, KeyIcon, Modal, UserIcon} from 'akeneo-design-system';
import {useTranslate} from '../../../../shared/translate';
import {CopiableCredential} from '../../../../settings/components/credentials/CopiableCredential';
import styled from 'styled-components';

export const CredentialList = styled.div`
    display: grid;
    grid-template-columns: 44px 120px repeat(2, auto);
`;

type Props = {
    handleRedirect: () => void;
    clientId: string;
    clientSecret: string | null;
};

export const RegenerateSecretNewCredentials = ({handleRedirect, clientId, clientSecret}: Props) => {
    const translate = useTranslate();

    return (
        <>
            <Modal.SectionTitle color='brand'>
                {translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.subtitle'
                )}
            </Modal.SectionTitle>
            <Modal.Title>
                {translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.title'
                )}
            </Modal.Title>
            <CredentialList>
                <CopiableCredential
                    icon={<UserIcon></UserIcon>}
                    label={translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.client_id'
                    )}
                >
                    {clientId}
                </CopiableCredential>
                <CopiableCredential
                    icon={<KeyIcon />}
                    label={translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.client_secret'
                    )}
                >
                    {clientSecret ?? ''}
                </CopiableCredential>
            </CredentialList>
            <Modal.BottomButtons>
                <Button level='primary' onClick={() => handleRedirect()}>
                    {translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.done_button'
                    )}
                </Button>
            </Modal.BottomButtons>
        </>
    );
};
