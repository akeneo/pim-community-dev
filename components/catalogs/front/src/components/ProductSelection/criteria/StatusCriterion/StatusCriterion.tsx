import React, {FC} from 'react';
import {CloseIcon, IconButton, List, SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import {StatusCriterionOperator, StatusCriterionState} from './types';
import styled from 'styled-components';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ErrorHelpers} from '../../components/ErrorHelpers';

const Fields = styled.div`
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    flex-grow: 1;
`;

const Field = styled.div`
    flex-basis: 200px;
    flex-shrink: 0;
`;

const StatusCriterion: FC<CriterionModule<StatusCriterionState>> = ({state, onChange, onRemove, errors}) => {
    const translateOperator = useOperatorTranslator();
    const translate = useTranslate();
    const hasError = Object.values(errors).filter(n => n).length > 0;

    return (
        <List.Row isMultiline>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.status.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Fields>
                    <Field>
                        <SelectInput
                            emptyResultLabel=''
                            openLabel=''
                            value={state.operator}
                            onChange={v => onChange({...state, operator: v as StatusCriterionOperator})}
                            clearable={false}
                            invalid={!!errors.operator}
                            data-testid='operator'
                        >
                            <SelectInput.Option value={Operator.EQUALS}>
                                {translateOperator(Operator.EQUALS)}
                            </SelectInput.Option>
                            <SelectInput.Option value={Operator.NOT_EQUAL}>
                                {translateOperator(Operator.NOT_EQUAL)}
                            </SelectInput.Option>
                        </SelectInput>
                    </Field>
                    <Field>
                        <SelectInput
                            emptyResultLabel=''
                            openLabel=''
                            value={state.value.toString()}
                            onChange={v => onChange({...state, value: v === 'true'})}
                            clearable={false}
                            invalid={!!errors.value}
                            data-testid='value'
                        >
                            <SelectInput.Option value='true'>
                                {translate('akeneo_catalogs.product_selection.criteria.status.enabled')}
                            </SelectInput.Option>
                            <SelectInput.Option value='false'>
                                {translate('akeneo_catalogs.product_selection.criteria.status.disabled')}
                            </SelectInput.Option>
                        </SelectInput>
                    </Field>
                </Fields>
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
            {hasError && (
                <List.RowHelpers>
                    <ErrorHelpers errors={errors} />
                </List.RowHelpers>
            )}
        </List.Row>
    );
};

export {StatusCriterion};
