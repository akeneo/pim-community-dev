import styled from 'styled-components';
import {PropsWithTheme} from '../theme';
import infoIconUrl from '../assets/icons/info.svg';
import warningIconUrl from '../assets/icons/warning.svg';

export const InlineHelper = styled.div<{info?: true; warning?: true}>`
    background: url(${props => (props.warning ? warningIconUrl : infoIconUrl)}) no-repeat left center;
    color: ${({theme}: PropsWithTheme) => theme.color.slateGrey};
    font-size: 11px;
    padding-left: 26px;

    a {
        color: ${({theme}: PropsWithTheme) => theme.color.blue};
        font-weight: 700;
        text-decoration: underline;
    }
`;
