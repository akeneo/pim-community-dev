import React from 'react';
import styled from 'styled-components';
import {Translate} from '../../../shared/translate';
import {CheckIcon, getColor} from 'akeneo-design-system';

export const FormControlSuccess = ({success}: {success: string}) => (
    <OkStatus>
        <CheckIcon />
        <Translate id={success} />
    </OkStatus>
);

const OkStatus = styled.span`
    display: flex;
    align-items: center;
    margin-top: 3px;
    color: ${getColor('green', 100)};
    background-size: 20px;
    background-position: top left;
`;
