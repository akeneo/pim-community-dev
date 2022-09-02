import React, {FC} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {CriterionModule} from '../../models/Criterion';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CompletenessCriterionState} from './types';
import {CompletenessOperatorInput} from './CompletenessOperatorInput';
import {CompletenessValueInput} from './CompletenessValueInput';
import {CompletenessLocaleInput} from './CompletenessLocaleInput';
import {CompletenessScopeInput} from './CompletenessScopeInput';
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

const CompletenessCriterion: FC<CriterionModule<CompletenessCriterionState>> = ({
    state,
    errors,
    onChange,
    onRemove,
}) => {
    const translate = useTranslate();
    const hasError = Object.values(errors).filter(n => n).length > 0;

    return (
        <List.Row isMultiline>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.completeness.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Fields>
                    <Field>
                        <CompletenessOperatorInput state={state} onChange={onChange} isInvalid={!!errors.operator} />
                    </Field>
                    <Field>
                        <CompletenessValueInput state={state} onChange={onChange} isInvalid={!!errors.value} />
                    </Field>
                    <Field>
                        <CompletenessScopeInput state={state} onChange={onChange} isInvalid={!!errors.scope} />
                    </Field>
                    <Field>
                        <CompletenessLocaleInput state={state} onChange={onChange} isInvalid={!!errors.locale} />
                    </Field>
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

export {CompletenessCriterion};
