import React, {FC} from 'react';
import styled from 'styled-components';
import {HelperLink, SmallHelper} from '../../common/components';
import {Translate} from '../../shared/translate';

export const EventSubscriptionHelper: FC = () => (
    <Container>
        <SmallHelper>
            <Translate id='akeneo_connectivity.connection.webhook.helper.message' />
            &nbsp;
            <HelperLink
                href='https://help.akeneo.com/pim/serenity/articles/manage-event-subscription.html'
                target='_blank'
                rel='noopener noreferrer'
            >
                <Translate id='akeneo_connectivity.connection.webhook.helper.link' />
            </HelperLink>
        </SmallHelper>
    </Container>
);

const Container = styled.div`
    padding-bottom: 20px;
`;
