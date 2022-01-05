import React, {useCallback} from 'react';
import {useHistory} from 'react-router';
import {Modal, AppIllustration, Button, getColor, getFontSize} from 'akeneo-design-system';
import styled from '../../common/styled-with-theme';
import {useTranslate} from '../../shared/translate';
import {useRouter} from '../../shared/router/use-router';

const Subtitle = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;

const Title = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 28px;
    margin: 0;
`;

const Helper = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    line-height: 18px;
    margin: 17px 0 17px 0;
`;

const Link = styled.a`
    color: ${getColor('brand', 100)};
    text-decoration: underline;
`;

export const TestAppCreatePage = () => {
    const history = useHistory();
    const generateUrl = useRouter();
    const translate = useTranslate();

    // TODO : handle the click
    const handleCreate = () => null;

    const handleCancel = useCallback(() => {
        history.push(generateUrl('akeneo_connectivity_connection_connect_marketplace'));
    }, [history, generateUrl]);

    return (
        <Modal onClose={handleCancel} illustration={<AppIllustration />} closeTitle={translate('pim_common.cancel')}>
            <Subtitle>
                {translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.subtitle')}
            </Subtitle>
            <Title>{translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.title')}</Title>
            <Helper>
                <p>
                    {translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.description')}
                    <span className='cline-any cline-neutral'>&nbsp;</span>
                    <Link href={'https://help.akeneo.com/pim/articles/manage-your-apps.html#create-a-test-app'}>
                        {translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.link')}
                    </Link>
                </p>
            </Helper>
            <Modal.BottomButtons>
                <Button onClick={handleCancel} level='tertiary'>
                    {translate('pim_common.cancel')}
                </Button>
                <Button onClick={handleCreate} level='primary'>
                    {translate('pim_common.create')}
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};
