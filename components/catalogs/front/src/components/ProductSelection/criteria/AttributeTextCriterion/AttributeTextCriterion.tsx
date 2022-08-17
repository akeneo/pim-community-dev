import React, {FC} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {CriterionModule} from '../../models/Criterion';
import {AttributeTextCriterionState} from './types';
import {useAttribute} from '../../hooks/useAttribute';
import {ErrorHelpers} from '../../components/ErrorHelpers';
import {AttributeTextOperatorInput} from './AttributeTextOperatorInput';
import {AttributeTextValueInput} from './AttributeTextValueInput';
import {ScopeInput} from '../../components/ScopeInput';
import {LocaleInput} from '../../components/LocaleInput';
import {CriterionFields, CriterionField} from '../../components/CriterionFields';
import {Operator} from '../../models/Operator';

const AttributeTextCriterion: FC<CriterionModule<AttributeTextCriterionState>> = ({
    state,
    errors,
    onChange,
    onRemove,
}) => {
    const {data: attribute} = useAttribute(state.field);
    const hasError = Object.values(errors).filter(n => n).length > 0;
    const showValueInput = [
        Operator.EQUALS,
        Operator.NOT_EQUAL,
        Operator.CONTAINS,
        Operator.DOES_NOT_CONTAIN,
        Operator.STARTS_WITH,
    ].includes(state.operator);

    return (
        <List.Row>
            <List.TitleCell width={150}>{attribute?.label}</List.TitleCell>
            <List.Cell width='auto'>
                <CriterionFields>
                    <CriterionField>
                        <AttributeTextOperatorInput state={state} onChange={onChange} isInvalid={!!errors.operator} />
                    </CriterionField>
                    {showValueInput && (
                        <CriterionField>
                            <AttributeTextValueInput state={state} onChange={onChange} isInvalid={!!errors.value} />
                        </CriterionField>
                    )}
                    {attribute?.scopable && (
                        <CriterionField width={140}>
                            <ScopeInput state={state} onChange={onChange} isInvalid={!!errors.scope} />
                        </CriterionField>
                    )}
                    {attribute?.localizable && (
                        <CriterionField width={140}>
                            <LocaleInput
                                state={state}
                                onChange={onChange}
                                isInvalid={!!errors.locale}
                                isScopable={attribute.scopable}
                            />
                        </CriterionField>
                    )}
                </CriterionFields>
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
