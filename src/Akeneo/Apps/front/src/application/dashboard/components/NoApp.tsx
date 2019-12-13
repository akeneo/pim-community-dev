import React from 'react';
import styled from 'styled-components';
import imgUrl from '../../common/assets/illustrations/api.svg';
import {PropsWithTheme} from '../../common/theme';
import {Translate} from '../../shared/translate';

export const NoApp = () => (
    <Container>
        <Image src={imgUrl} />
        <Title>
            <Translate id='pim_apps.no_app.title' />
        </Title>
    </Container>
);

const Container = styled.div`
    text-align: center;
`;

const Image = styled.img`
    width: 256px;
`;

const Title = styled.div`
    height: 36px;
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.title};
    color: ${({theme}: PropsWithTheme) => theme.color.grey140};
`;
