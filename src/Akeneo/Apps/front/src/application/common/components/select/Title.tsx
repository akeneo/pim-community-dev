import styled from 'styled-components';
import {PropsWithTheme} from '../../theme';

export const Title = styled.div`
    border-bottom: 1px solid ${({theme}: PropsWithTheme) => theme.color.purple100};
    color: ${({theme}: PropsWithTheme) => theme.color.purple100};
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.default};
    line-height: 26px;
    margin-bottom: 12px;
    padding-bottom: 5px;
    text-transform: uppercase;
`;
