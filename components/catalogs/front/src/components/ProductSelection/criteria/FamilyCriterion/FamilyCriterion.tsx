import React, {FC, useEffect, useState} from 'react';
import {CloseIcon, IconButton, List, MultiSelectInput, SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import styled from 'styled-components';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FamilyCriterionOperator, FamilyCriterionState} from './types';
import {useFamilies} from '../../hooks/useFamilies';

const Inputs = styled.div`
    display: flex;
    gap: 20px;
`;

const FamilyCriterion: FC<CriterionModule<FamilyCriterionState>> = ({state, onChange, onRemove}) => {
    const translateOperator = useOperatorTranslator();
    const translate = useTranslate();
    const [showFamilies, setShowFamilies] = useState<boolean>(false);
    const {data, fetchNextPage} = useFamilies();
    console.log(data);

    useEffect(() => {
        setShowFamilies([Operator.IN_LIST, Operator.NOT_IN_LIST].includes(state.operator));
    }, [state]);

    const options = data?.pages.map((families) => {
        return (
            <>
                {families.data.map(({code, label}) => {
                    return (
                        <MultiSelectInput.Option
                            title={label}
                            value={code}
                            key={code}
                        >
                            {label}
                        </MultiSelectInput.Option>
                    );
                })}
            </>
        );
    });

    return (
        <List.Row>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.status.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Inputs>
                    <SelectInput
                        emptyResultLabel=''
                        openLabel=''
                        value={state.operator}
                        onChange={v => onChange({...state, operator: v as FamilyCriterionOperator})}
                        clearable={false}
                        data-testid='operator'
                    >
                        <SelectInput.Option value={Operator.IS_EMPTY}>
                            {translateOperator(Operator.IS_EMPTY)}
                        </SelectInput.Option>
                        <SelectInput.Option value={Operator.IS_NOT_EMPTY}>
                            {translateOperator(Operator.IS_NOT_EMPTY)}
                        </SelectInput.Option>
                        <SelectInput.Option value={Operator.IN_LIST}>
                            {translateOperator(Operator.IN_LIST)}
                        </SelectInput.Option>
                        <SelectInput.Option value={Operator.NOT_IN_LIST}>
                            {translateOperator(Operator.NOT_IN_LIST)}
                        </SelectInput.Option>
                    </SelectInput>
                    {showFamilies && <MultiSelectInput
                        onChange={v => onChange({...state, value: v})}
                        emptyResultLabel={translate('akeneo_catalogs.product_selection.criteria.family.no_matches')}
                        openLabel=''
                        onNextPage={fetchNextPage}
                        placeholder=''
                        value={state.value}
                        removeLabel=''
                        data-testid='value'
                    >
                        {/*{options}*/}
                        <MultiSelectInput.Option
                            title="Option 0"
                            value="Option 0"
                        >
                            Option 0
                        </MultiSelectInput.Option>
                    </MultiSelectInput>
                    }
                </Inputs>
            </List.Cell>
            <List.RemoveCell>
                <IconButton ghost='borderless' level='tertiary' icon={<CloseIcon />} title='' onClick={onRemove} />
            </List.RemoveCell>
        </List.Row>
    );
};

export {FamilyCriterion};
