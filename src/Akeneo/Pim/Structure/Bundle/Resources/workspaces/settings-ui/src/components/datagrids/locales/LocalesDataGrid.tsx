import React, {FC, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useFilteredLocales} from '../../../hooks';
import {Locale} from '../../../models';
import {SearchBar, useDebounceCallback} from '@akeneo-pim-community/shared';
import {Table} from 'akeneo-design-system';
import {NoResults} from '../../shared';
import styled from 'styled-components';
import {FollowLocaleHandler} from '../../../user-actions';

type Props = {
  locales: Locale[];
  followLocale?: FollowLocaleHandler;
};

const LocalesSearchBar = styled(SearchBar)`
  margin: 10px 0 20px;
`;

const LocalesDataGrid: FC<Props> = ({locales, followLocale}) => {
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const {filteredLocales, search} = useFilteredLocales(locales);

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  return (
    <>
      <LocalesSearchBar
        count={filteredLocales.length}
        searchValue={searchString === undefined ? '' : searchString}
        placeholder={translate('pim_enrich.entity.locale.grid.filters.search_placeholder')}
        onSearchChange={onSearch}
      />
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
