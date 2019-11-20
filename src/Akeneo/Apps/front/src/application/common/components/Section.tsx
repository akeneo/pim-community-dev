import React, {PropsWithChildren, ReactNode} from 'react';
import styled from 'styled-components';
import {PropsWithTheme} from '../theme';

const SectionContainer = styled.header`
    display: flex;
    height: 44px;
    border-bottom: 1px solid #11324d;
`;

const Title = styled.div`
    flex-grow: 1;
    line-height: 44px;
    font-size: 13px;
    text-transform: uppercase;
    color: ${({theme}: PropsWithTheme) => theme.color.darkBlue};
`;

export const Section = ({title, children}: PropsWithChildren<{title: ReactNode}>) => (
    <SectionContainer>
        <Title>{title}</Title>
        {children}
    </SectionContainer>
);
