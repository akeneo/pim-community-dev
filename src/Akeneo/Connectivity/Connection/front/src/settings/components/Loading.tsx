import React from 'react';
import loaderUrl from '../../common/assets/icons/loader.svg';
import styled from '../../common/styled-with-theme';

const Container = styled.div`
    background-color: ${({theme}) => theme.color.grey140};
    display: flex;
    flex-direction: row;
    padding: 0 5px;
    position: absolute;
    bottom: 5px;
    right: 0;
`;
const Loader = styled.img`
    width: 14px;
`;
const DisplayRatio = styled.span`
    font-size: ${({theme}) => theme.fontSize.small};
    color: #c7cbd4;
    margin-left: 5px;
`;

interface Props {
    ratio: number;
}

export const Loading = ({ratio}: Props) => {
    return (
        <Container>
            <Loader src={loaderUrl} />
            <DisplayRatio>{ratio.toString() + '%'}</DisplayRatio>
        </Container>
    );
};
