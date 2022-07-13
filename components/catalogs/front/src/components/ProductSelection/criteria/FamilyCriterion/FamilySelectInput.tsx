import React, {FC, useState} from 'react';
import {Helper, MultiSelectInput} from 'akeneo-design-system';
import {FamilyCriterionState} from './types';
import {useInfiniteFamilies} from '../../hooks/useInfiniteFamilies';
import {useFamiliesByCodes} from '../../hooks/useFamiliesByCodes';
import {useUniqueFamilies} from '../../hooks/useUniqueFamilies';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
    state: FamilyCriterionState;
    onChange: (state: FamilyCriterionState) => void;
    error: string | undefined;
};

const FamilySelectInput: FC<Props> = ({state, onChange, error}) => {
    const translate = useTranslate();
    const [search, setSearch] = useState<string>();
    const {data: selection} = useFamiliesByCodes(state.value);
    const {data: results, fetchNextPage} = useInfiniteFamilies({search: search});
    const families = useUniqueFamilies(selection, results);

    return (
        <>
            <MultiSelectInput
                value={state.value}
                emptyResultLabel={translate('akeneo_catalogs.product_selection.criteria.family.no_matches')}
                openLabel={translate('akeneo_catalogs.product_selection.action.open')}
                removeLabel={translate('akeneo_catalogs.product_selection.action.remove')}
                placeholder={translate('akeneo_catalogs.product_selection.criteria.family.placeholder')}
                onChange={v => onChange({...state, value: v})}
                onNextPage={fetchNextPage}
                onSearchChange={setSearch}
                invalid={error !== undefined}
                data-testid='value'
            >
                {families.map(family => (
                    <MultiSelectInput.Option key={family.code} title={family.label} value={family.code}>
                        {family.label}
                    </MultiSelectInput.Option>
                ))}
            </MultiSelectInput>
            {error !== undefined && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </>
    );
};

export {FamilySelectInput};
