import styled from 'styled-components';
import { CoreButton } from '../CoreButton';
import * as theme from '../../../theme/akeneoTheme';

const SecondaryButton = styled(CoreButton)`
    color: ${theme.color.white};
    border-color: ${theme.color.blue100};
    background-color: ${theme.color.blue100};
    &:hover {
        border-color: ${theme.color.blue120};
        background-color: ${theme.color.blue120};
    }
    &:active {
        border-color: ${theme.color.blue140};
        background-color: ${theme.color.blue140};
    }
    &:focus {
        border-color: ${theme.color.blue100};
    }
    &:disabled {
        border-color: ${theme.color.blue40};
        background-color: ${theme.color.blue40};
    }
`;

export { SecondaryButton };
