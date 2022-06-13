import React, {FC} from 'react';
import {List, SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import {StatusCriterionOperator, StatusCriterionState} from './types';
import styled from 'styled-components';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {useTranslate} from '@akeneo-pim-community/shared';

const Inputs = styled.div`
    display: flex;
    gap: 20px;
`;

const StatusCriterion: FC<CriterionModule<StatusCriterionState>> = ({state, onChange}) => {
    const translateOperator = useOperatorTranslator();
    const translate = useTranslate();

    return (
        <List.Row>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.status.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Inputs>
                    <SelectInput
                        emptyResultLabel=''
                        openLabel=''
                        value={state.operator}
                        onChange={v => onChange({...state, operator: v as StatusCriterionOperator})}
                        clearable={false}
                        data-testid='operator'
                    >
                        <SelectInput.Option value={Operator.EQUALS}>
                            {translateOperator(Operator.EQUALS)}
                        </SelectInput.Option>
                        <SelectInput.Option value={Operator.NOT_EQUAL}>
                            {translateOperator(Operator.NOT_EQUAL)}
                        </SelectInput.Option>
                    </SelectInput>
                    <SelectInput
                        emptyResultLabel=''
                        openLabel=''
                        value={state.value.toString()}
                        onChange={v => onChange({...state, value: v === 'true'})}
                        clearable={false}
                        data-testid='value'
                    >
                        <SelectInput.Option value='true'>
                            {translate('akeneo_catalogs.product_selection.criteria.status.enabled')}
                        </SelectInput.Option>
                        <SelectInput.Option value='false'>
                            {translate('akeneo_catalogs.product_selection.criteria.status.disabled')}
                        </SelectInput.Option>
                    </SelectInput>
                </Inputs>
            </List.Cell>
        </List.Row>
    );
};

export {StatusCriterion};
