import React, {FC, useEffect, useState} from 'react';
import {CloseIcon, IconButton, List} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FamilyCriterionState} from './types';
import {FamilyOperatorInput} from './FamilyOperatorInput';
import {FamilySelectInput} from './FamilySelectInput';
import {ErrorHelpers} from '../../components/ErrorHelpers';
import {CriterionField, CriterionFields} from '../../components/CriterionFields';

const FamilyCriterion: FC<CriterionModule<FamilyCriterionState>> = ({state, errors, onChange, onRemove}) => {
    const translate = useTranslate();
    const [showFamilies, setShowFamilies] = useState<boolean>(false);
    const hasError = Object.values(errors).filter(n => n).length > 0;

    useEffect(() => {
        setShowFamilies([Operator.IN_LIST, Operator.NOT_IN_LIST].includes(state.operator));
    }, [state.operator]);

    return (
        <List.Row isMultiline>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.family.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <CriterionFields>
                    <CriterionField>
                        <FamilyOperatorInput state={state} onChange={onChange} isInvalid={!!errors.operator} />
                    </CriterionField>
                    {showFamilies && (
                        <CriterionField width={300}>
                            <FamilySelectInput state={state} onChange={onChange} isInvalid={!!errors.value} />
                        </CriterionField>
                    )}
                </CriterionFields>
            </List.Cell>
            <List.RemoveCell>
                <IconButton ghost='borderless' level='tertiary' icon={<CloseIcon />} title='' onClick={onRemove} />
            </List.RemoveCell>
            {hasError && (
                <List.RowHelpers>
                    <ErrorHelpers errors={errors} />
                </List.RowHelpers>
            )}
        </List.Row>
    );
};

export {FamilyCriterion};
