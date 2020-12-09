import React, {FC, useCallback, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useFilteredLocales} from '../../../hooks';
import {Locale} from '../../../models';
import {debounce} from 'lodash';
import {SearchBar} from '@akeneo-pim-community/shared/src';
import {Table} from 'akeneo-design-system';
import {NoResults} from '../../shared';
import styled from 'styled-components';

type Props = {
  locales: Locale[];
};

const LocalesSearchBar = styled(SearchBar)`
  margin: 10px 0 20px;
`;

const LocalesDataGrid: FC<Props> = ({locales}) => {
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const {filteredLocales, search} = useFilteredLocales(locales);

  const debouncedSearch = useCallback(debounce(search, 300), [locales]);

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
              <Table.Row key={locale.code}>
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
