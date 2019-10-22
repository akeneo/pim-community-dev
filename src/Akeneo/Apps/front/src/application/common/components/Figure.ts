import styled from 'styled-components';

export const Figure = styled.div`
    width: 140px;
    height: 165px;
    cursor: pointer;
`;

export const FigureImage = styled.img`
    max-width: 140px;
    max-height: 140px;
    border: 1px solid #a1a9b7;
`;

export const FigureCaption = styled.div`
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;
