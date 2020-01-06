import styled from 'styled-components';

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
