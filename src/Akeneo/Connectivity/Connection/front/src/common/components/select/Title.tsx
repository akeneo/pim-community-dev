import styled from '../../styled-with-theme';

export const Title = styled.div`
    border-bottom: 1px solid ${({theme}) => theme.color.purple100};
    color: ${({theme}) => theme.color.purple100};
    font-size: ${({theme}) => theme.fontSize.default};
    line-height: 26px;
    margin-bottom: 12px;
    padding-bottom: 5px;
    text-transform: uppercase;
`;
