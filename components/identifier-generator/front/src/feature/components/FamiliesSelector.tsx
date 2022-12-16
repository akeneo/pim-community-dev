import React, {FC} from 'react';
import {MultiSelectInput} from 'akeneo-design-system';
import {useGetFamilies, usePaginatedFamilies} from '../hooks/useFamilies';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Family, FamilyCode} from '../models';

type FamiliesSelectorProps = {
  familyCodes: FamilyCode[],
  onChange: (familyCodes: FamilyCode[]) => void;
};

const FamiliesSelector: FC<FamiliesSelectorProps> = ({familyCodes, onChange}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {families, handleNextPage, handleSearchChange} = usePaginatedFamilies();
  const {data: selectedValues, isLoading} = useGetFamilies({codes: familyCodes});

  // Avoid blinking of values when selecting a new one
  const [debouncedSelectedValues, setDebouncedSelectedValues] = React.useState<Family[]>([]);
  React.useEffect(() => {
    if (!isLoading) {
      setDebouncedSelectedValues(selectedValues as Family[]);
    }
  }, [selectedValues, isLoading]);

  const getFamiliesList = [
    ...(families || []),
    ...(debouncedSelectedValues || []).filter(family => !(families || []).map(f => f.code).includes(family.code))
  ];

  console.log(JSON.stringify(getFamiliesList));

  return <MultiSelectInput
    emptyResultLabel={translate('pim_common.no_result')}
    placeholder="Please select at least one family"
    removeLabel={translate('pim_common.remove')}
    openLabel={translate('pim_common.open')}
    onNextPage={handleNextPage}
    onSearchChange={handleSearchChange}
    onChange={onChange}
    value={familyCodes}
  >
    {getFamiliesList.map(family => (
      <MultiSelectInput.Option value={family.code} key={family.code}>
      {getLabel(family.labels, catalogLocale, family.code)}
    </MultiSelectInput.Option>))}
  </MultiSelectInput>;
};

export {FamiliesSelector};
