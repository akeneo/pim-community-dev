import styled from 'styled-components';
import { CoreButton } from '../CoreButton';
import * as theme from '../../../theme/akeneoTheme';

const DangerButton = styled(CoreButton)`
    color: ${theme.color.white};
    border-color: ${theme.color.red100};
    background-color: ${theme.color.red100};
    &:hover {
        border-color: ${theme.color.red120};
        background-color: ${theme.color.red120};
    }
    &:active {
        border-color: ${theme.color.red140};
        background-color: ${theme.color.red140};
    }
    &:focus {
        border-color: ${theme.color.red100};
    }
    &:disabled {
        border-color: ${theme.color.red40};
        background-color: ${theme.color.red40};
    }
`;

export { DangerButton };
