import React, {FC, useState} from 'react';
import {MultiSelectInput} from 'akeneo-design-system';
import {FamilyCriterionState} from './types';
import {useInfiniteFamilies} from '../../hooks/useInfiniteFamilies';

type Props = {
    state: FamilyCriterionState;
    onChange: (state: FamilyCriterionState) => void;
};

const FamilySelectInput: FC<Props> = ({state, onChange}) => {
    const [search, setSearch] = useState<string>();
    const {data, fetchNextPage} = useInfiniteFamilies({search: search});

    return (
        <MultiSelectInput
            value={state.value}
            emptyResultLabel=''
            openLabel=''
            removeLabel=''
            onChange={v => onChange({...state, value: v})}
            onNextPage={fetchNextPage}
            onSearchChange={setSearch}
        >
            {data?.map(family => (
                <MultiSelectInput.Option key={family.code} title={family.label} value={family.code}>
                    {family.label}
                </MultiSelectInput.Option>
            ))}
        </MultiSelectInput>
    );
};

export {FamilySelectInput};
