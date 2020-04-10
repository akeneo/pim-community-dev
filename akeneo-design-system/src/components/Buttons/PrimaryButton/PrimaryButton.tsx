import styled from 'styled-components'
import { CoreButton } from '../CoreButton'
import * as theme from '../../../theme/akeneoTheme';

const PrimaryButton = styled(CoreButton)`
    color: ${theme.color.white};
    border-color: ${theme.color.green100};
    background-color: ${theme.color.green100};
    &:hover {
        border-color: ${theme.color.green120};
        background-color: ${theme.color.green120};
    }
    &:active {
        border-color: ${theme.color.green140};
        background-color: ${theme.color.green140};
    }
    &:focus {
        border-color: ${theme.color.green100};
    }
    &:disabled {
        border-color: ${theme.color.green40};
        background-color: ${theme.color.green40};
    }
`
export { PrimaryButton }
