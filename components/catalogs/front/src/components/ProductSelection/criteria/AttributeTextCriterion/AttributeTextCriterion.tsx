import React, {FC} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {CriterionModule} from '../../models/Criterion';
import {AttributeTextCriterionState} from './types';
import {useAttribute} from '../../hooks/useAttribute';
import styled from 'styled-components';
import {ErrorHelpers} from '../../components/ErrorHelpers';
import {AttributeTextOperatorInput} from './AttributeTextOperatorInput';
import {AttributeTextValueInput} from './AttributeTextValueInput';
import {ScopeInput} from '../../components/ScopeInput';

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

const AttributeTextCriterion: FC<CriterionModule<AttributeTextCriterionState>> = ({
    state,
    errors,
    onChange,
    onRemove,
}) => {
const {data: attribute} = useAttribute(state.field);
    const hasError = Object.values(errors).filter(n => n).length > 0;

    return (
        <List.Row>
            <List.TitleCell width={150}>{attribute?.label}</List.TitleCell>
            <List.Cell width='auto'>
                <Fields>
                    <Field>
                        <AttributeTextOperatorInput state={state} onChange={onChange} isInvalid={!!errors.operator} />
                    </Field>
                    <Field>
                        <AttributeTextValueInput state={state} onChange={onChange} isInvalid={!!errors.value} />
                    </Field>
                    {attribute.scopable && (
                        <Field>
                            <ScopeInput state={state} onChange={onChange} isInvalid={!!errors.scope} />
                        </Field>
                    )}
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

export {AttributeTextCriterion};
