import styled from 'styled-components';
import infoIconUrl from '../assets/icons/info.svg';
import warningIconUrl from '../assets/icons/warning.svg';
import {PropsWithTheme} from '../theme';

export const InlineHelper = styled.div<{info?: true; warning?: true}>`
    background: url(${props => (props.warning ? warningIconUrl : infoIconUrl)}) no-repeat left center;
    color: ${({theme}: PropsWithTheme) => theme.color.grey120};
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.small};
    padding-left: 26px;

    a {
        color: ${({theme}: PropsWithTheme) => theme.color.blue100};
        font-weight: 700;
        text-decoration: underline;
    }
`;
