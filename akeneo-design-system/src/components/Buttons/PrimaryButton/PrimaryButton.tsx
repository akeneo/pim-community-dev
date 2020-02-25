import React, { FunctionComponent } from 'react'
import styled from 'styled-components'

import { CoreButton } from '../CoreButton'


// text color	white
// background-color	green-100
// hover background-color	green-120
// active background-color	green-140
// focus border-color	$color
// disabled background-color	green-40
// disabled text color	white

const PrimaryButton = styled(CoreButton)`
    color: white;
    background-color: #67B373;
    &:hover {
        background-color: #528f5c;
    }
    &:active {
        background-color: #3d6b45;
    }
    &:focus {
        border-color: blue;
    }
    &:disabled {
        background-color: #67b373;
    }

`

export { PrimaryButton }
