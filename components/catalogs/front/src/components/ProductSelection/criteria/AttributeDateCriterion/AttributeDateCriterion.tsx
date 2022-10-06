import React, {FC} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {CriterionModule} from '../../models/Criterion';
import {AttributeDateCriterionState} from './types';
import {useAttribute} from '../../hooks/useAttribute';
import {ErrorHelpers} from '../../components/ErrorHelpers';
import {AttributeDateOperatorInput} from './AttributeDateOperatorInput';
import {AttributeDateValueSingleInput} from './AttributeDateValueSingleInput';
import {AttributeDateValueMultiInput} from './AttributeDateValueMultiInput';
import {ScopeInput} from '../../components/ScopeInput';
import {LocaleInput} from '../../components/LocaleInput';
import {CriterionFields, CriterionField} from '../../components/CriterionFields';
import {Operator} from '../../models/Operator';
import {useTranslate} from '@akeneo-pim-community/shared';

const AttributeDateCriterion: FC<CriterionModule<AttributeDateCriterionState>> = ({
    state,
    errors,
    onChange,
    onRemove,
}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(state.field);
    const hasError = Object.values(errors).filter(n => n).length > 0;
    const showValueSingleInput = [
        Operator.EQUALS,
        Operator.NOT_EQUAL,
        Operator.LOWER_THAN,
        Operator.GREATER_THAN,
    ].includes(state.operator);
    const showValueMultiInput = [Operator.BETWEEN, Operator.NOT_BETWEEN].includes(state.operator);

    return (
        <List.Row>
            <List.TitleCell width={150}>{attribute?.label}</List.TitleCell>
            <List.Cell width='auto'>
                <CriterionFields>
                    <CriterionField>
                        <AttributeDateOperatorInput state={state} onChange={onChange} isInvalid={!!errors.operator} />
                    </CriterionField>
                    {showValueSingleInput && (
                        <CriterionField width={300}>
                            <AttributeDateValueSingleInput
                                state={state}
                                onChange={onChange}
                                isInvalid={!!errors.value}
                            />
                        </CriterionField>
                    )}
                    {showValueMultiInput && (
                        <AttributeDateValueMultiInput state={state} onChange={onChange} isInvalid={!!errors.value} />
                    )}
                    {attribute?.scopable && (
                        <CriterionField width={120}>
                            <ScopeInput state={state} onChange={onChange} isInvalid={!!errors.scope} />
                        </CriterionField>
                    )}
                    {attribute?.localizable && (
                        <CriterionField width={120}>
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

export {AttributeDateCriterion};
