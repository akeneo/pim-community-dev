import React, {FC, useEffect, useMemo, useState} from 'react';
import {Helper, MultiSelectInput} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {OptionCode} from '../models/option';
import {useGetSelectOptions, usePaginatedOptions} from '../hooks/useGetSelectOptions';
import {AttributeCode} from '../models';
import {Styled} from './Styled';
import {useIdentifierGeneratorAclContext} from '../context';

type Props = {
  attributeCode: AttributeCode;
  optionCodes: OptionCode[];
  onChange: (optionCodes: OptionCode[]) => void;
};

const SimpleSelectOptionsSelector: FC<Props> = ({attributeCode, optionCodes, onChange}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [invalidOptionCodes, setInvalidOptionCodes] = useState<OptionCode[]>([]);
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  const {
    data: selectedOptions,
    error: errorDuringGet,
    isLoading: isGetLoading,
  } = useGetSelectOptions({attributeCode, codes: optionCodes});
  const {
    options: paginatedOptions,
    handleNextPage,
    handleSearchChange,
    error: errorDuringPagination,
    isLoading: isPaginationLoading,
  } = usePaginatedOptions(attributeCode);

  useEffect(() => {
    if (!isGetLoading && !isPaginationLoading && !errorDuringGet && !errorDuringPagination) {
      const result = optionCodes?.filter(code => !selectedOptions?.map(({code}) => code).includes(code));
      setInvalidOptionCodes(result);
    }
  }, [errorDuringGet, errorDuringPagination, isGetLoading, isPaginationLoading, optionCodes, selectedOptions]);

  const optionsList = useMemo(() => {
    const missingSelectedOptions =
      selectedOptions?.filter(({code: selectedCode}) => !paginatedOptions?.some(({code}) => selectedCode === code)) ||
      [];
    return (paginatedOptions || []).concat(missingSelectedOptions);
  }, [paginatedOptions, selectedOptions]);

  if (errorDuringGet || errorDuringPagination) {
    return <Helper level={'error'}>{translate('pim_error.general')}</Helper>;
  }

  return (
    <Styled.MultiSelectCondition
      emptyResultLabel={translate('pim_common.no_result')}
      placeholder={translate('pim_identifier_generator.selection.settings.select_option.placeholder')}
      removeLabel={translate('pim_common.remove')}
      openLabel={translate('pim_common.open')}
      onNextPage={handleNextPage}
      onSearchChange={handleSearchChange}
      onChange={onChange}
      value={optionCodes}
      invalidValue={invalidOptionCodes}
      readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
    >
      {optionsList?.map(option => (
        <MultiSelectInput.Option value={option.code} key={option.code}>
          {getLabel(option.labels, catalogLocale, option.code)}
        </MultiSelectInput.Option>
      ))}
    </Styled.MultiSelectCondition>
  );
};

export {SimpleSelectOptionsSelector};
