import React from 'react';
import {ApplyButton} from '.';
import {Translate} from '../../shared/translate';
import imageUrl from '../assets/illustrations/NewAPI.svg';
import styled from '../styled-with-theme';

const Title = styled.div`
    color: ${({theme}) => theme.color.grey140};
    font-size: 28px;
`;

const Content = styled.div`
    color: ${({theme}) => theme.color.grey120};
    font-size: ${({theme}) => theme.fontSize.big};
    margin: 10px auto 20px;
`;

const Container = styled.div`
    width: 740px;
    margin: 10px auto;
    text-align: center;
`;

const Illustration = styled.img`
    margin: 0 auto;
    width: 128px;
`;

export const RuntimeError = () => {
    const handleClick = () => window.location.reload();
    return (
        <Container>
            <Illustration src={imageUrl} />
            <Title>
                <Translate id='akeneo_connectivity.connection.runtime_error.error_message' />
            </Title>
            <Content>
                <Translate id='akeneo_connectivity.connection.runtime_error.reload_helper' />
            </Content>
            <ApplyButton onClick={handleClick}>
                <Translate id='akeneo_connectivity.connection.runtime_error.reload_button' />
            </ApplyButton>
        </Container>
    );
};
