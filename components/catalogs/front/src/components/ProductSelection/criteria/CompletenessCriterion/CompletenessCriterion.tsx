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

const Fields = styled.div`
    display: flex;
    gap: 20px;
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

    return (
        <List.Row>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.family.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Fields>
                    <Field>
                        <CompletenessOperatorInput state={state} onChange={onChange} error={errors.operator} />
                    </Field>
                    <Field>
                        <CompletenessValueInput state={state} onChange={onChange} error={errors.value} />
                    </Field>
                    <Field>
                        <CompletenessScopeInput state={state} onChange={onChange} error={errors.scope} />
                    </Field>
                    <Field>
                        <CompletenessLocaleInput state={state} onChange={onChange} error={errors.locale} />
                    </Field>
                </Fields>
            </List.Cell>
            <List.RemoveCell>
                <IconButton ghost='borderless' level='tertiary' icon={<CloseIcon />} title='' onClick={onRemove} />
            </List.RemoveCell>
        </List.Row>
    );
};

export {CompletenessCriterion};
