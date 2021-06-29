import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';

export const IconButton = styled.button`
    background-color: transparent;
    border: none;
    cursor: pointer;
    display: inline-flex;
    height: 24px;
    justify-content: center;
    opacity: 0.8;
    padding: 0;
    transition: opacity 0.1s ease-in;
    width: 24px;
    color: ${getColor('grey', 100)};

    :hover {
        opacity: 1;
    }

    :focus {
        outline: none;
    }

    svg {
        width: 18px;
        height: 18px;
    }
`;
