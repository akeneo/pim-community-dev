import styled from '../../styled-with-theme';

export const GhostButton = styled.button`
    background-color: white;
    border-radius: 16px;
    border: 1px solid ${({theme}) => theme.color.grey100};
    color: ${({theme}) => theme.color.grey120};
    cursor: pointer;
    font-size: ${({theme}) => theme.fontSize.big};
    height: 32px;
    line-height: 30px;
    min-width: 32px;
    padding: 0 16px;
    text-transform: uppercase;
    transition: all 0.1s ease;
    white-space: nowrap;

    :hover {
        background-color: ${({theme}) => theme.color.grey20};
        color: ${({theme}) => theme.color.grey140};
    }

    :disabled {
        border: 1px solid ${({theme}) => theme.color.grey60};
        color: ${({theme}) => theme.color.grey80};
        cursor: not-allowed;
    }

    :focus {
        box-shadow: 0px 0px 2px 0px ${({theme}) => theme.color.blue100};
        outline: none;
    }

    :active {
        border: 1px solid ${({theme}) => theme.color.grey140};
        color: ${({theme}) => theme.color.grey140};
    }
`;
