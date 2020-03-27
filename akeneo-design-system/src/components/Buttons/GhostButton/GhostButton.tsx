import styled from 'styled-components';
import { CoreButton } from '../CoreButton';
import * as theme from '../../../theme/akeneoTheme';

const GhostButton = styled(CoreButton)`
    color: ${theme.color.grey100};
    border-color: ${theme.color.grey80};
    background-color: ${theme.color.white};
    &:hover {
        border-color: ${theme.color.grey60};
    }
    &:active {
        border-color: ${theme.color.grey60};
    }
    &:disabled {
        border-color: ${theme.color.grey60};
        color: ${theme.color.grey80}
    }
`;

export { GhostButton };
