import React, {useCallback, useState} from 'react';
import {useHistory} from 'react-router';
import {Modal, AppIllustration, getColor, getFontSize, ClientErrorIllustration} from 'akeneo-design-system';
import styled from '../../common/styled-with-theme';
import {useTranslate} from '../../shared/translate';
import {useRouter} from '../../shared/router/use-router';
import {CreateTestAppForm} from '../components/TestApp/CreateTestAppForm';
import {TestAppCredentials} from '../../model/Apps/test-app-credentials';
import {CreateTestAppCredentials} from '../components/TestApp/CreateTestAppCredentials';
import {useAppDeveloperMode} from '../hooks/use-app-developer-mode';

const Subtitle = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;
const InfoBlock = styled.div`
    font-size: ${getFontSize('default')};
    max-width: 940px;
    margin: 10px auto;
    text-align: center;
    height: 80vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
`;
const IllustrationContainer = styled.div`
    position: relative;
    margin-bottom: 15px;
`;
const ErrorCode = styled.span`
    right: 335px;
    bottom: 35px;
    position: absolute;
    font-size: ${getFontSize('title')};
    font-weight: bold;
    color: ${getColor('blue', 120)};
`;
const ErrorMessage = styled.div`
    text-align: center;
    border: 1px solid #c1422f;
    background: #f2cfca;
    color: #983425;
    border-radius: 2px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    margin-bottom: 8px;
`;

export const TestAppCreatePage = () => {
    const history = useHistory();
    const generateUrl = useRouter();
    const translate = useTranslate();
    const [credentials, setCredentials] = useState<TestAppCredentials | null>(null);
    const isDeveloperModeEnabled = useAppDeveloperMode();

    const handleCloseModal = useCallback(() => {
        history.push(generateUrl('akeneo_connectivity_connection_connect_marketplace'));
    }, [history, generateUrl]);

    if (!isDeveloperModeEnabled) {
        return (
            <InfoBlock>
                <IllustrationContainer>
                    <ClientErrorIllustration width='auto' height='auto' />
                    <ErrorCode>404</ErrorCode>
                </IllustrationContainer>
                <h1>{translate('error.exception', {status_code: '404'})}</h1>
                <ErrorMessage>
                    {translate('akeneo_connectivity.connection.connect.marketplace.test_apps.errors.page_not_found')}
                </ErrorMessage>
            </InfoBlock>
        );
    }

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
