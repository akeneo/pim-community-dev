import {css} from 'styled-components';
import styled from '../../styled-with-theme';

export default styled.td<{collapsing?: boolean}>`
    border-bottom: 1px solid ${({theme}) => theme.color.grey60};
    color: ${({theme}) => theme.color.grey120};
    padding: 15px 20px;

    ${({collapsing}) =>
        collapsing &&
        css`
            width: 1px;
            white-space: nowrap;
        `}
`;
