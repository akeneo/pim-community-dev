import React, {FC, useCallback, useEffect, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useLocalesIndexState} from '../../../hooks';
import {Locale} from '../../../models';
import {DataGrid} from '../../shared';
import {debounce} from 'lodash';
import {SearchBar} from '@akeneo-pim-community/shared/src';

type Props = {
  locales: Locale[];
};

const LocalesDataGrid: FC<Props> = ({locales}) => {
  const {compare} = useLocalesIndexState();
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const [filteredLocales, setFilteredLocales] = useState<Locale[]>([]);

  useEffect(() => {
    setFilteredLocales(locales);
  }, [locales]);

  const debouncedSearch = useCallback(
    debounce((searchValue: string) => {
      setFilteredLocales(
        locales.filter((locale: Locale) => locale.code.toLocaleLowerCase().includes(searchValue.toLowerCase().trim()))
      );
    }, 300),
    [locales]
  );

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  if (searchString !== '' && filteredLocales.length === 0) {
    return (
      <>
        <SearchBar
          count={filteredLocales.length}
          searchValue={searchString === undefined ? '' : searchString}
          onSearchChange={onSearch}
        />
        <>TODO No result</>
      </>
    );
  }

  return (
    <>
      <SearchBar
        count={filteredLocales.length}
        searchValue={searchString === undefined ? '' : searchString}
        onSearchChange={onSearch}
      />
      <DataGrid dataSource={filteredLocales} compareData={compare} isFilterable={true}>
        <DataGrid.HeaderRow>
          <DataGrid.Cell>{translate('pim_enrich.entity.locale.grid.columns.code')}</DataGrid.Cell>
        </DataGrid.HeaderRow>
        <DataGrid.Body>
          {filteredLocales.map(locale => (
            <DataGrid.Row key={locale.code} data={locale}>
              <DataGrid.Cell rowHeader>{locale.code}</DataGrid.Cell>
            </DataGrid.Row>
          ))}
        </DataGrid.Body>
      </DataGrid>
    </>
  );
};

export {LocalesDataGrid};
