import styled from '../../styled-with-theme';

const Link = styled.a`
    color: ${({theme}) => theme.color.purple100};
    cursor: pointer;
    text-decoration: underline;
`;

export {Link};
