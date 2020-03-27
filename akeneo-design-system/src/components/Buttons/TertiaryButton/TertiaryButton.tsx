import styled from 'styled-components';
import { CoreButton } from '../CoreButton';
import * as theme from '../../../theme/akeneoTheme';

const SecondaryButton = styled(CoreButton)`
    color: ${theme.color.white};
    border-color: ${theme.color.grey100};
    background-color: ${theme.color.grey100};
    &:hover {
        border-color: ${theme.color.grey120};
        background-color: ${theme.color.grey120};
    }
    &:active {
        border-color: ${theme.color.grey140};
        background-color: ${theme.color.grey140};
    }
    &:focus {
        border-color: ${theme.color.grey100};
    }
    &:disabled {
        border-color: ${theme.color.grey40};
        background-color: ${theme.color.grey40};
    }
`;

export { SecondaryButton };
