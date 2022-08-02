import React, {FC, useEffect, useState} from 'react';
import {CloseIcon, Helper, IconButton, List} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import {CategoryCriterionState} from './types';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

const CategoryCriterion: FC<CriterionModule<CategoryCriterionState>> = ({state, errors, onChange, onRemove}) => {
    const translate = useTranslate();

    return (
        <List.Row>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.category.label')}
            </List.TitleCell>
            <List.Cell width='auto'></List.Cell>
            <List.RemoveCell>
                <IconButton ghost='borderless' level='tertiary' icon={<CloseIcon />} title='' onClick={onRemove} />
            </List.RemoveCell>
        </List.Row>
    );
};

export {CategoryCriterion};
