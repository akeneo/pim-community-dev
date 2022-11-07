import React, {useEffect, useState} from 'react';
import {MultiSelectInput} from 'akeneo-design-system';
import {useTranslate, userContext, useDebounceCallback} from '@akeneo-pim-community/shared';
import {Family, FamilyCode, getFamilyLabel} from '../models';
import {useFetchers} from '../contexts';
import styled from 'styled-components';

const limit = 20;

type Props = {
  onChange: (value: FamilyCode[]) => void;
};

const Option = styled(MultiSelectInput.Option)<{$display: boolean}>`
  display: ${$display => ($display ? 'block' : 'none')};
`;

const filterObjectKeys = (obj: {[key: string]: any}, keys: string[]) =>
  Object.keys(obj)
    .filter(objKey => keys.includes(objKey))
    .reduce((filteredObj, objKey: string) => {
      return Object.assign(filteredObj, {[objKey]: obj[objKey]});
    }, {});

const MultiFamilyInput = ({onChange}: Props) => {
  const translate = useTranslate();
  const fetcher = useFetchers();
  const labels = {};
  labels[userContext.get('catalogLocale')] = translate(
    'akeneo.performance_analytics.control_panel.multi_input.all_families'
  );
  const defaultFamilies = {'<all_families>': {code: '<all_families>', labels: labels}};
  const [currentFamilies, setCurrentFamilies] = useState<{[key: string]: Family}>(defaultFamilies);
  // The display of selected items is based on the texts of the options. So if we remove some options
  // (in the case of a search for instance), we lost the label of the selected items.
  // To fix that, we keep in selectedFamilies all the selected families in order to render a hidden option.
  const [selectedFamilies, setSelectedFamilies] = useState<{[key: string]: Family}>(defaultFamilies);
  const [values, setValues] = useState<FamilyCode[]>(['<all_families>']);
  const [page, setPage] = useState<number>(1);
  const [maxFamiliesReach, setMaxFamiliesReach] = useState<boolean>(false);
  const [isFetching, setIsFetching] = useState<boolean>(false);
  const [searchValue, setSearchValue] = useState<string>('');
  const [previousSearchValue, setPreviousSearchValue] = useState<string>('');

  let isMounted = true;
  const fetchFamilies = (page: number, searchValue: string, previousSearchValue: string) => {
    if (!isMounted) return;

    setIsFetching(true);

    const fetchFamilies = async (page: number, search: undefined | string) => {
      return await fetcher.family.fetchFamilies(limit, page, search);
    };

    fetchFamilies(page, searchValue).then(async newFamilies => {
      if (Object.keys(newFamilies).length < limit) {
        setMaxFamiliesReach(true);
      }

      if (previousSearchValue !== searchValue) {
        setCurrentFamilies(newFamilies);
        setPreviousSearchValue(searchValue);
      } else {
        setCurrentFamilies({...currentFamilies, ...newFamilies});
      }

      setIsFetching(false);
    });
  };

  const debouncedFetchFamilies = useDebounceCallback(fetchFamilies, 500);

  useEffect(() => {
    debouncedFetchFamilies(page, searchValue, previousSearchValue);

    return () => {
      // eslint-disable-next-line react-hooks/exhaustive-deps
      isMounted = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fetcher.family, page, searchValue]);

  const handleChange = (newValues: FamilyCode[]) => {
    if (newValues.length === 0 || (!values.includes('<all_families>') && newValues.includes('<all_families>'))) {
      if (typeof currentFamilies['<all_families>'] === 'undefined') {
        setCurrentFamilies({...defaultFamilies, ...currentFamilies});
      }
      newValues = ['<all_families>'];
    }

    if (values.includes('<all_families>') && newValues.length > 1) {
      newValues = newValues.filter(value => value !== '<all_families>');
    }

    const newSelectedFamilies = {
      ...filterObjectKeys(selectedFamilies, newValues),
      ...filterObjectKeys(currentFamilies, newValues),
    };

    setValues(newValues);
    setSelectedFamilies(newSelectedFamilies);
    onChange(newValues);
  };

  return (
    <MultiSelectInput
      value={values}
      onChange={handleChange}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      removeLabel={translate('pim_common.remove')}
      onNextPage={() => {
        // onNextPage is triggered too many times, the fetch has not the time to be executed, so we added a "isFetching" state
        if (!maxFamiliesReach && !isFetching) {
          setIsFetching(true);
          setPage(page + 1);
        }
      }}
      onSearchChange={(newSearchValue: string) => {
        // onSearchChange is triggered too many times (for example when we open or close the select) => we need to know when the search is really changed
        if (previousSearchValue === newSearchValue) {
          return;
        }
        setIsFetching(true);
        setPage(1);
        setMaxFamiliesReach(false);
        setPreviousSearchValue(searchValue);
        setSearchValue(newSearchValue);
      }}
    >
      {Object.values({...selectedFamilies, ...currentFamilies}).map((family: Family) => (
        <Option value={family.code} key={family.code} $display={typeof currentFamilies[family.code] !== 'undefined'}>
          {getFamilyLabel(family, userContext.get('catalogLocale'))}
        </Option>
      ))}
    </MultiSelectInput>
  );
};

export {MultiFamilyInput};
