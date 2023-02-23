import React, {FC} from 'react';
import {useTranslate} from '../../../shared/translate';
import styled from '../../../common/styled-with-theme';
import {Button, getColor, Modal, UserIcon, KeyIcon} from 'akeneo-design-system';
import {CopiableCredential} from '../../../settings/components/credentials/CopiableCredential';
import {CredentialList} from '../../../settings/components/credentials/Credential';
import {CustomAppCredentials} from '../../../model/Apps/custom-app-credentials';

type Props = {
    onClose: () => void;
    credentials: CustomAppCredentials;
};

const Title = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 28px;
    margin: 0 0 10px 0;
`;

const CustomAppCredentialList = styled(CredentialList)`
    margin: 10px 0 0 0;
`;

export const CreateCustomAppCredentials: FC<Props> = ({onClose, credentials}) => {
    const translate = useTranslate();

    return (
        <>
            <Title>
                {translate('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.title')}
            </Title>
            <CustomAppCredentialList withIcon={true}>
                <CopiableCredential
                    icon={<UserIcon></UserIcon>}
                    label={translate(
                        'akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.client_id'
                    )}
                >
                    {credentials.clientId}
                </CopiableCredential>
                <CopiableCredential
                    icon={<KeyIcon />}
                    label={translate(
                        'akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.client_secret'
                    )}
                >
                    {credentials.clientSecret}
                </CopiableCredential>
            </CustomAppCredentialList>

            <Modal.BottomButtons>
                <Button onClick={onClose} level='primary'>
                    {translate('pim_common.done')}
                </Button>
            </Modal.BottomButtons>
        </>
    );
};
