import * as React from 'react';
import warningIconUrl from '../assets/icons/warning.svg';
import styled from '../styled-with-theme';

export const Figure = styled.div`
    cursor: pointer;
`;

const ImageContainer = styled.div`
    border: 1px solid ${({theme}) => theme.color.grey100};
    height: 140px;
    overflow: hidden;
    display: flex;
`;

const Image = styled.img`
    width: 100%;
    display: block;
    box-sizing: border-box;
    object-fit: cover;
`;

const Mask = styled.div`
    position: relative;
    ::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        background-color: ${({theme}) => theme.color.grey140};
        opacity: 0;
        box-sizing: border-box;
        transition: opacity 0.2s ease-in-out;
    }
    :hover {
        ::after {
            opacity: 0.4;
        }
    }
`;

export const FigureImage = ({...props}) => (
    <>
        <Mask>
            <ImageContainer>
                <Image {...props} />
            </ImageContainer>
        </Mask>
    </>
);

export const FigureCaption = styled.div<{warning?: boolean}>`
    ${props => {
        if (undefined === props.warning || !props.warning) {
            return;
        }
        return `
            background-size: 20px;
            background-image: url(${warningIconUrl});
            background-repeat: no-repeat;
            background-position: left;
            padding-left: 25px;
        `;
    }}
    margin-top: 5px;
    height: 20px;
    line-height: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;
