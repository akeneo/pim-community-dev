import styled from '../../styled-with-theme';

export default styled.tr`
    height: 54px;

    &:hover {
        background-color: ${({theme}) => theme.color.grey20};
    }
`;
