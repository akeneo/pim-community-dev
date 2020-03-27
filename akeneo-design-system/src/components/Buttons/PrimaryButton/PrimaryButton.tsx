import styled from 'styled-components'

import { CoreButton } from '../CoreButton'


const PrimaryButton = styled(CoreButton)`
    color: white;
    border-color: #67B373;
    background-color: #67B373;
    &:hover {
        border-color: #528f5c;
        background-color: #528f5c;
    }
    &:active {
        border-color: #3d6b45;
        background-color: #3d6b45;
    }
    &:focus {
        border-color: blue;
    }
    &:disabled {
        border-color: #c2e1c7;
        background-color: #c2e1c7;
    }
`

export { PrimaryButton }
