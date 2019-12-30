import React, {ReactNode} from 'react';
import styled from 'styled-components';
import iconUrl from '../assets/icons/info.svg';
import {PropsWithTheme} from '../theme';

const SubsectionHint = styled.div`
    align-items: center;
    background: ${({theme}: PropsWithTheme) => theme.color.blue10};
    display: flex;
`;

const HintIcon = styled.div`
    background-image: url(${iconUrl});
    background-position: center;
    background-repeat: no-repeat;
    background-size: 20px;
    flex-shrink: 0;
    height: 20px;
    margin: 12px;
    width: 20px;
`;

const HintTitle = styled.div`
    border-left: 1px solid ${({theme}: PropsWithTheme) => theme.color.grey80};
    flex-grow: 1;
    font-weight: 600;
    padding-left: 16px;
    margin: 10px 0;
    margin-right: 10px;
`;

export const SmallHelper = ({children}: {children: ReactNode}) => (
    <SubsectionHint className='AknSubsection'>
        <HintIcon />
        <HintTitle>{children}</HintTitle>
    </SubsectionHint>
);
