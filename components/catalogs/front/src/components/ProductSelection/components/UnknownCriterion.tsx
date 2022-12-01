import React, {FC, memo} from 'react';
import {AnyCriterionState} from '../models/Criterion';
import {CriterionErrors} from '../models/CriterionErrors';
import {ErrorHelpers} from './ErrorHelpers';
import {CloseIcon, getColor, IconButton, List} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useOperatorTranslator} from '../hooks/useOperatorTranslator';
import styled from 'styled-components';

const Title = styled.span`
    color: ${getColor('purple', 100)};
    font-style: italic;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    margin: 0 5px 0 0;
`;
const Operator = styled.span`
    text-transform: lowercase;
    margin: 0 5px 0 0;
`;
const Value = styled.span`
    color: ${getColor('purple', 100)};
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
`;

type Props = {
    state: AnyCriterionState;
    onRemove: () => void;
};

export const UnknownCriterion: FC<Props> = memo(({state, onRemove}) => {
    const translate = useTranslate();
    const translateOperator = useOperatorTranslator();
    const errors: CriterionErrors = {
        field: translate('akeneo_catalogs.product_selection.criteria.unknown'),
    };

    return (
        <List.Row>
            <List.Cell width='auto'>
                <Title>[{state.field}]</Title>
                <Operator>{translateOperator(state.operator)}</Operator>
                <Value>{String(state.value)}</Value>
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
            <List.RowHelpers>
                <ErrorHelpers errors={errors} />
            </List.RowHelpers>
        </List.Row>
    );
});
