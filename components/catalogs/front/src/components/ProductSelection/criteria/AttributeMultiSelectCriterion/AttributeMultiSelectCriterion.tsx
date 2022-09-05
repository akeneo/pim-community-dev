import React, {FC} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {CriterionModule} from '../../models/Criterion';
import {AttributeMultiSelectCriterionState} from './types';
import {useAttribute} from '../../hooks/useAttribute';
import {ErrorHelpers} from '../../components/ErrorHelpers';
import {AttributeMultiSelectOperatorInput} from './AttributeMultiSelectOperatorInput';
import {AttributeMultiSelectValueInput} from './AttributeMultiSelectValueInput';
import {ScopeInput} from '../../components/ScopeInput';
import {LocaleInput} from '../../components/LocaleInput';
import {CriterionField, CriterionFields} from '../../components/CriterionFields';
import {Operator} from '../../models/Operator';
import {useTranslate} from '@akeneo-pim-community/shared';

const AttributeMultiSelectCriterion: FC<CriterionModule<AttributeMultiSelectCriterionState>> = ({
    state,
    errors,
    onChange,
    onRemove,
}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(state.field);
    const hasError = Object.values(errors).filter(n => n).length > 0;
    const showValueInput = [Operator.IN_LIST, Operator.NOT_IN_LIST].includes(state.operator);

    return (
        <List.Row>
            <List.TitleCell width={150}>{attribute?.label}</List.TitleCell>
            <List.Cell width='auto'>
                <CriterionFields>
                    <CriterionField>
                        <AttributeMultiSelectOperatorInput
                            state={state}
                            onChange={onChange}
                            isInvalid={!!errors.operator}
                        />
                    </CriterionField>
                    {showValueInput && (
                        <CriterionField>
                            <AttributeMultiSelectValueInput
                                state={state}
                                onChange={onChange}
                                isInvalid={!!errors.value}
                            />
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

export {AttributeMultiSelectCriterion};
