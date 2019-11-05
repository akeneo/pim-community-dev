import React from 'react';
import {Translate} from '../../shared/translate';
import styled from 'styled-components';
import imgUrl from '../../common/assets/illustrations/api.svg';

const Container = styled.div`
    text-align: center;
`;

const Title = styled.div`
    height: 36px;
    font-size: 30px;
    color: #11324d;
`;

const Message = styled.div`
    height: 21px;
    font-size: 17px;
`;

const Link = styled.a`
    color: #9452ba;
    cursor: pointer;
    text-decoration: underline #9452ba;
`;

const Image = styled.img`
    width: 256px;
`;

export const NoApp = ({onCreate}: {onCreate: () => void}) => (
    <Container>
        <Image src={imgUrl} />
        <Title>
            <Translate id='pim_apps.no_app.title' />
        </Title>
        <Message>
            <Translate id='pim_apps.no_app.message' />
            &nbsp;
            <Link onClick={onCreate}>
                <Translate id='pim_apps.no_app.message_link' />
            </Link>
        </Message>
    </Container>
);
