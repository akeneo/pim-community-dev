import styled from 'styled-components';
import infoIconUrl from '../assets/icons/info.svg';
import warningIconUrl from '../assets/icons/warning.svg';
import {PropsWithTheme} from '../theme';

export const InlineHelper = styled.div<{info?: true; warning?: true}>`
    background-size: 20px;
    background-image: url(${props => (props.warning ? warningIconUrl : infoIconUrl)});
    background-repeat: no-repeat;
    background-position: left top;
    color: ${({theme}: PropsWithTheme) => theme.color.grey120};
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.small};
    line-height: 20px;
    padding-left: 26px;

    a {
        color: ${({theme}: PropsWithTheme) => theme.color.blue100};
        font-weight: 700;
        text-decoration: underline;
    }
`;
