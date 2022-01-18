import React, {FC, useCallback} from 'react';
import {useTranslate} from '../../../shared/translate';
import styled from '../../../common/styled-with-theme';
import {Button, getColor, Helper, Modal, UserIcon, KeyIcon} from 'akeneo-design-system';
import {CopiableCredential} from '../../../settings/components/credentials/CopiableCredential';
import {CredentialList} from '../../../settings/components/credentials/Credential';
import {TestAppCredentials} from '../../../model/Apps/test-app-credentials';

type Props = {
    onClose: () => void;
    credentials: TestAppCredentials;
    setCredentials: (credentials: TestAppCredentials | null) => void;
};

const Title = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 28px;
    margin: 0 0 10px 0;
`;

const TestAppCredentialList = styled(CredentialList)`
    margin: 10px 0 0 0;
`;

export const CreateTestAppCredentials: FC<Props> = ({onClose, credentials, setCredentials}) => {
    const translate = useTranslate();

    const onDone = useCallback(() => {
        setCredentials(null);

        onClose();
    }, [onClose]);

    return (
        <>
            <Title>
                {translate('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.credentials.title')}
            </Title>

            <Helper level='warning'>
                {translate('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.credentials.warning')}
            </Helper>

            <TestAppCredentialList withIcon={true}>
                <CopiableCredential
                    icon={<UserIcon></UserIcon>}
                    label={translate(
                        'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.credentials.client_id'
                    )}
                >
                    {credentials.clientId}
                </CopiableCredential>
                <CopiableCredential
                    icon={<KeyIcon />}
                    label={translate(
                        'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.credentials.client_secret'
                    )}
                >
                    {credentials.clientSecret}
                </CopiableCredential>
            </TestAppCredentialList>

            <Modal.BottomButtons>
                <Button onClick={onDone} level='primary'>
                    {translate('pim_common.done')}
                </Button>
            </Modal.BottomButtons>
        </>
    );
};
