import styled from '../../styled-with-theme';

export default styled.th`
    border-bottom: 1px solid ${({theme}) => theme.color.grey120};
    color: ${({theme}) => theme.color.grey140};
    font-weight: normal;
    padding: 0 20px;
    text-align: left;
`;
