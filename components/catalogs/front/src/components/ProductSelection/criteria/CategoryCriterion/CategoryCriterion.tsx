import React, {FC} from 'react';
import styled from 'styled-components';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import {CategoryCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CategoryOperatorInput} from './CategoryOperatorInput';
import {CategorySelectInput} from './CategorySelectInput';
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

const LargeField = styled.div`
    flex-basis: 300px;
    flex-shrink: 0;
`;

const CategoryCriterion: FC<CriterionModule<CategoryCriterionState>> = ({state, errors, onChange, onRemove}) => {
    const translate = useTranslate();
    const hasError = Object.values(errors).filter(n => n).length > 0;
    const showCategories = Operator.UNCLASSIFIED !== state.operator;

    return (
        <List.Row isMultiline>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.category.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Fields>
                    <Field>
                        <CategoryOperatorInput state={state} onChange={onChange} isInvalid={!!errors.operator} />
                    </Field>
                    {showCategories && (
                        <LargeField>
                            <CategorySelectInput state={state} onChange={onChange} isInvalid={!!errors.value} />
                        </LargeField>
                    )}
                </Fields>
            </List.Cell>
            <List.RemoveCell>
                <IconButton
                    ghost='borderless'
                    level='tertiary'
                    icon={<CloseIcon />}
                    title={translate('akeneo_catalogs.product_selection.action.remove')}
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

export {CategoryCriterion};
