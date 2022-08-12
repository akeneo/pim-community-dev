import React, {FC} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {CriterionModule} from '../../models/Criterion';
import {AttributeTextCriterionState} from './types';
import {useAttribute} from '../../hooks/useAttribute';

const AttributeTextCriterion: FC<CriterionModule<AttributeTextCriterionState>> = ({state, onRemove}) => {
    const {data: attribute} = useAttribute(state.field);

    return (
        <List.Row>
            <List.TitleCell width={150}>{attribute?.label}</List.TitleCell>
            <List.Cell width='auto'>
                <ul>
                    <li>code: {attribute?.code}</li>
                    <li>type: {attribute?.type}</li>
                    <li>localizable: {attribute?.localizable ? 'true' : 'false'}</li>
                    <li>scopable: {attribute?.scopable ? 'true' : 'false'}</li>
                </ul>
            </List.Cell>
            <List.RemoveCell>
                <IconButton
                    ghost='borderless'
                    level='tertiary'
                    icon={<CloseIcon />}
                    title='remove'
                    onClick={onRemove}
                />
            </List.RemoveCell>
        </List.Row>
    );
};

export {AttributeTextCriterion};
