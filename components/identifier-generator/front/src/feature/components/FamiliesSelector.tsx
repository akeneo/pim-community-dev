import React, {FC, useEffect, useState} from 'react';
import {Helper, MultiSelectInput} from 'akeneo-design-system';
import {useGetFamilies, usePaginatedFamilies} from '../hooks/useGetFamilies';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Family, FamilyCode} from '../models';
import {Unauthorized} from '../errors';
import {Styled} from './Styled';
import {useIdentifierGeneratorAclContext} from '../context';

type FamiliesSelectorProps = {
  familyCodes: FamilyCode[];
  onChange: (familyCodes: FamilyCode[]) => void;
};

const FamiliesSelector: FC<FamiliesSelectorProps> = ({familyCodes, onChange}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();
  const {
    families: paginatedFamilies,
    handleNextPage,
    handleSearchChange,
    error: errorDuringPagination,
  } = usePaginatedFamilies();
  const {data: selectedFamilies, isLoading, error: errorDuringGet} = useGetFamilies({codes: familyCodes});

  // Avoid blinking of values when selecting a new one
  const [debouncedSelectedFamilies, setDebouncedSelectedFamilies] = useState<Family[]>([]);
  useEffect(() => {
    if (!isLoading && !errorDuringGet) {
      setDebouncedSelectedFamilies(selectedFamilies as Family[]);
    }
  }, [selectedFamilies, isLoading, errorDuringGet]);

  const [debouncedInvalidFamilyCodes, setDebouncedInvalidFamilyCodes] = useState<FamilyCode[]>([]);
  useEffect(() => {
    if (!isLoading && !errorDuringGet) {
      setDebouncedInvalidFamilyCodes(
        familyCodes.filter(code => !(selectedFamilies as Family[]).map(family => family.code).includes(code))
      );
    }
  }, [selectedFamilies, isLoading, errorDuringGet, familyCodes]);

  const familiesList = [
    ...(paginatedFamilies || []),
    ...(debouncedSelectedFamilies || []).filter(
      family => !(paginatedFamilies || []).map(family => family.code).includes(family.code)
    ),
  ];

  if (errorDuringGet || errorDuringGet) {
    if (errorDuringGet instanceof Unauthorized || errorDuringPagination instanceof Unauthorized) {
      return <Helper level={'error'}>{translate('pim_error.unauthorized_list_families')}</Helper>;
    }
    return <Helper level={'error'}>{translate('pim_error.general')}</Helper>;
  }

  return (
    <Styled.MultiSelectCondition
      emptyResultLabel={translate('pim_common.no_result')}
      placeholder={translate('pim_identifier_generator.selection.settings.family.placeholder')}
      removeLabel={translate('pim_common.remove')}
      openLabel={translate('pim_common.open')}
      onNextPage={handleNextPage}
      onSearchChange={handleSearchChange}
      onChange={onChange}
      value={familyCodes}
      invalidValue={debouncedInvalidFamilyCodes}
      readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
    >
      {familiesList.map(family => (
        <MultiSelectInput.Option value={family.code} key={family.code}>
          {getLabel(family.labels, catalogLocale, family.code)}
        </MultiSelectInput.Option>
      ))}
    </Styled.MultiSelectCondition>
  );
};

export {FamiliesSelector};
