import * as React from 'react';
import styled from 'styled-components';

export const Figure = styled.div`
    cursor: pointer;
`;

const Image = styled.img`
    width: 100%;
    border: 1px solid #a1a9b7;
    display: block;
    box-sizing: border-box;
`;

const Mask = styled.div`
    position: relative;
    :hover {
        ::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            background-color: #11324d;
            opacity: 0.4;
            box-sizing: border-box;
        }
    }
`;

export const FigureImage = ({...props}) => (
    <>
        <Mask>
            <Image {...props} />
        </Mask>
    </>
);

export const FigureCaption = styled.div`
    margin-top: 5px;
    height: 20px;
    line-height: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;
