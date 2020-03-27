import styled from 'styled-components'
import { CoreButton } from '../CoreButton'

const SecondaryButton = styled(CoreButton)`
    color: white;
    background-color: #5992c7;
    &:hover {
        background-color: #47749f;
    }
    &:active {
        background-color: #355777;
    }
    &:focus {
        border-color: #5992c7;
    }
    &:disabled {
        background-color: #bdd3e9;
    }
`

export { SecondaryButton }
