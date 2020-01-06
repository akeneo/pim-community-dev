import * as React from 'react';
import styled from 'styled-components';
import {PropsWithTheme} from '../theme';

export const Figure = styled.div`
    cursor: pointer;
`;

const ImageContainer = styled.div`
    border: 1px solid ${({theme}: PropsWithTheme) => theme.color.grey100};
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
        background-color: ${({theme}: PropsWithTheme) => theme.color.grey140};
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

export const FigureCaption = styled.div`
    margin-top: 5px;
    height: 20px;
    line-height: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;
