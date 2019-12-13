import React from 'react';
import styled from 'styled-components';
import imgUrl from '../../common/assets/illustrations/api.svg';
import {PropsWithTheme} from '../../common/theme';
import {Translate} from '../../shared/translate';

const Container = styled.div`
    text-align: center;
`;

const Title = styled.div`
    height: 36px;
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.title};
    color: ${({theme}: PropsWithTheme) => theme.color.grey140};
`;

const Message = styled.div`
    height: 21px;
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.bigger};
`;

const Link = styled.a`
    color: #9452ba;
    cursor: pointer;
    text-decoration: underline ${({theme}: PropsWithTheme) => theme.color.purple100};
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
