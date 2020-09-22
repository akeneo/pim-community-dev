import React, {FC, useCallback} from 'react';
import styled from 'styled-components';
import {MoveIcon} from '@akeneo-pim-community/shared';
import {TableRow, TableCell} from '../shared';
import {AttributeGroup} from '../../models';
import {useAttributeGroupLabel, useRedirectToAttributeGroup} from '../../hooks';

type Props = {
    group: AttributeGroup;
    isSortable: boolean;
};

const Label = styled.span`
    width: 71px;
    height: 16px;
    color: ${({theme}) => theme.color.purple100};
    font-size: ${({theme}) => theme.fontSize.default};
    font-family: ${({theme}) => theme.font.default};
    font-weight: bold;
    font-style: italic;
`;

const AttributeGroupRow: FC<Props> = ({group, isSortable}) => {
    const label = useAttributeGroupLabel(group);
    const redirect = useRedirectToAttributeGroup();

    const handleRedirectToGroup = useCallback(() => {
        if (isSortable) {
            redirect(group);
        }
    }, [redirect, group, isSortable]);

    return (
        <TableRow onClick={handleRedirectToGroup}>
            {isSortable && (
                <TableCell>
                    <MoveIcon />
                </TableCell>
            )}
            <TableCell>
                <Label>{label}</Label>
            </TableCell>
        </TableRow>
    );
};

export {AttributeGroupRow};