import React, {FC, ReactNode} from 'react';
import styled from 'styled-components';
import defaultImgUrl from '../assets/illustrations/charts.svg';
import {PropsWithTheme} from '../theme';

type Props = {
    imgUrl?: string;
    title: ReactNode;
    children: ReactNode;
};

export const PageError: FC<Props> = ({imgUrl = defaultImgUrl, title, children}: Props) => (
    <Container>
        <Image src={imgUrl} />
        <Title>{title}</Title>
        <Message>{children}</Message>
    </Container>
);

const Container = styled.div`
    text-align: center;
`;

const Image = styled.img`
    width: 200px;
`;

const Title = styled.div`
    line-height: 28px;
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.title};
    color: ${({theme}: PropsWithTheme) => theme.color.grey140};
`;

const Message = styled.div`
    height: 21px;
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.bigger};
    margin-top: 5px;
`;
