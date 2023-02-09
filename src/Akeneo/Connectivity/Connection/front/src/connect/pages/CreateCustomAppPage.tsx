import React, {FC, useCallback, useState} from 'react';
import {useHistory} from 'react-router';
import {AppIllustration, getColor, getFontSize, Modal} from 'akeneo-design-system';
import styled from '../../common/styled-with-theme';
import {useTranslate} from '../../shared/translate';
import {useRouter} from '../../shared/router/use-router';
import {CreateTestAppForm} from '../components/TestApp/CreateTestAppForm';
import {TestAppCredentials} from '../../model/Apps/test-app-credentials';
import {CreateTestAppCredentials} from '../components/TestApp/CreateTestAppCredentials';

const Subtitle = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;

export const CreateCustomAppPage: FC = () => {
    const history = useHistory();
    const generateUrl = useRouter();
    const translate = useTranslate();
    const [credentials, setCredentials] = useState<TestAppCredentials | null>(null);

    const handleCloseModal = useCallback(() => {
        history.push(generateUrl('akeneo_connectivity_connection_connect_marketplace'));
    }, [history, generateUrl]);

    return (
        <Modal
            onClose={handleCloseModal}
            illustration={<AppIllustration />}
            closeTitle={translate('pim_common.cancel')}
        >
            <Subtitle>
                {translate('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.subtitle')}
            </Subtitle>
            {null === credentials && <CreateTestAppForm onCancel={handleCloseModal} setCredentials={setCredentials} />}
            {null !== credentials && (
                <CreateTestAppCredentials
                    onClose={handleCloseModal}
                    credentials={credentials}
                    setCredentials={setCredentials}
                />
            )}
        </Modal>
    );
};
