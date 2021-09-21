import React, {FC, useEffect, useRef, useState} from 'react';
import {useFilteredLocales} from '../../../hooks';
import {Locale} from '../../../models';
import {useDebounceCallback, useTranslate} from '@akeneo-pim-community/shared';
import {Search, Table, useAutoFocus} from 'akeneo-design-system';
import {NoResults} from '../../shared';
import styled from 'styled-components';
import {FollowLocaleHandler} from '../../../user-actions';

type Props = {
  locales: Locale[];
  followLocale?: FollowLocaleHandler;
  onLocaleCountChange: (newLocaleCount: number) => void;
};

const LocalesSearchBar = styled(Search)`
  margin: 10px 0 20px;
`;

const LocalesDataGrid: FC<Props> = ({locales, followLocale, onLocaleCountChange}) => {
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const {filteredLocales, search} = useFilteredLocales(locales);

  const debouncedSearch = useDebounceCallback(search, 300);
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  useEffect(() => {
    onLocaleCountChange(filteredLocales.length);
  }, [filteredLocales.length]);

  return (
    <>
      <LocalesSearchBar
        searchValue={searchString}
        placeholder={translate('pim_enrich.entity.locale.grid.filters.search_placeholder')}
        onSearchChange={onSearch}
        inputRef={inputRef}
      >
        <Search.ResultCount>
          {translate('pim_common.result_count', {itemsCount: filteredLocales.length}, filteredLocales.length)}
        </Search.ResultCount>
      </LocalesSearchBar>
      {searchString !== '' && filteredLocales.length === 0 ? (
        <NoResults
          title={translate('pim_datagrid.no_results', {entityHint: 'locale'})}
          subtitle={translate('pim_datagrid.no_results_subtitle')}
        />
      ) : (
        <Table className={'grid'}>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_enrich.entity.locale.grid.columns.code')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {filteredLocales.map(locale => (
              <Table.Row
                key={locale.code}
                onClick={followLocale !== undefined ? () => followLocale(locale) : undefined}
              >
                <Table.Cell rowTitle>{locale.code}</Table.Cell>
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
      )}
    </>
  );
};

export {LocalesDataGrid};
