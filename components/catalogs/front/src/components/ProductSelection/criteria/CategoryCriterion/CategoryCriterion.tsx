import React, {FC, useEffect, useState} from 'react';
import styled from 'styled-components';
import {CloseIcon, Helper, IconButton, List} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import {CategoryCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CategoryOperatorInput} from './CategoryOperatorInput';
import {CategorySelectInput} from './CategorySelectInput';

const Fields = styled.div`
    display: flex;
    gap: 20px;
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
    const [showCategories, setShowCategories] = useState<boolean>(false);

    useEffect(() => {
        setShowCategories(Operator.UNCLASSIFIED !== state.operator);
    }, [state.operator]);

    const errorHelpers = Object.keys(errors).map(key =>
        errors[key] === undefined || errors[key] === null ? null : (
            <Helper key={key} level='error'>
                {errors[key]}
            </Helper>
        )
    );

    return (
        <List.Row>
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
                            <CategorySelectInput state={state} onChange={onChange} isInvalid={!!errors.state} />
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
            {errorHelpers.length > 0 && <List.RowHelpers>{errorHelpers}</List.RowHelpers>}
        </List.Row>
    );
};

export {CategoryCriterion};
