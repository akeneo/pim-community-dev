import styled from 'styled-components'
import { CoreButton } from '../CoreButton'
import * as theme from '../../../theme/mainTheme';

const PrimaryButton = styled(CoreButton)`
    color: ${theme.color.white};
    background-color: ${theme.color.green100};
    &:hover {
        background-color: ${theme.color.green120};
    }
    &:active {
        background-color: ${theme.color.green140};
    }
    &:focus {
        border-color: ${theme.color.green100};
    }
    &:disabled {
        background-color: ${theme.color.green40};
    }
`
export { PrimaryButton }
