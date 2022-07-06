import React, {FC, useEffect, useState} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FamilyCriterionState} from './types';
import {FamilyOperatorInput} from './FamilyOperatorInput';
import {FamilySelectInput} from './FamilySelectInput';

const Inputs = styled.div`
    display: flex;
    gap: 20px;
`;

const FamilyCriterion: FC<CriterionModule<FamilyCriterionState>> = ({state, onChange, onRemove}) => {
    const translate = useTranslate();
    const [showFamilies, setShowFamilies] = useState<boolean>(false);

    useEffect(() => {
        setShowFamilies([Operator.IN_LIST, Operator.NOT_IN_LIST].includes(state.operator));
    }, [state.operator]);

    return (
        <List.Row>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.status.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Inputs>
                    <FamilyOperatorInput state={state} onChange={onChange} />
                    {showFamilies && <FamilySelectInput state={state} onChange={onChange} />}
                </Inputs>
            </List.Cell>
            <List.RemoveCell>
                <IconButton ghost='borderless' level='tertiary' icon={<CloseIcon />} title='' onClick={onRemove} />
            </List.RemoveCell>
        </List.Row>
    );
};

export {FamilyCriterion};
