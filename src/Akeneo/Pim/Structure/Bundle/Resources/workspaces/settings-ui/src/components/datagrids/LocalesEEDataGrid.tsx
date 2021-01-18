import React, {FC, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {FollowLocaleHandler, Locale, NoResults, useFilteredLocales} from '@akeneo-pim-community/settings-ui';
import {SearchBar, useDebounceCallback} from '@akeneo-pim-community/shared';
import {Badge, Table} from 'akeneo-design-system';
import styled from 'styled-components';
import {useLocalesDictionaryInfo} from '../../hooks';

const FeatureFlags = require('pim/feature-flags');

type Props = {
  locales: Locale[];
  followLocale?: FollowLocaleHandler;
};

const LocalesSearchBar = styled(SearchBar)`
  margin: 10px 0 20px;
`;

const LocalesEEDataGrid: FC<Props> = ({locales, followLocale}) => {
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const {filteredLocales, search} = useFilteredLocales(locales);
  const {getDictionaryTotalWords} = useLocalesDictionaryInfo(locales);

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  const localeColumnWidth = FeatureFlags.isEnabled('dictionary') ? '380px' : undefined;

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
            {/* @ts-ignore | @fixme: width props definition */}
            <Table.HeaderCell width={localeColumnWidth}>
              {translate('pim_enrich.entity.locale.grid.columns.code')}
            </Table.HeaderCell>
            {FeatureFlags.isEnabled('dictionary') && (
              <Table.HeaderCell>
                {translate('pimee_enrich.entity.locale.grid.columns.dictionary_words_count.title')}
              </Table.HeaderCell>
            )}
          </Table.Header>
          <Table.Body>
            {filteredLocales.map(locale => {
              const totalWords = getDictionaryTotalWords(locale);

              return (
                <Table.Row
                  key={locale.code}
                  onClick={followLocale !== undefined ? () => followLocale(locale) : undefined}
                >
                  {/* @ts-ignore | @fixme: width props definition */}
                  <Table.Cell rowTitle width={localeColumnWidth}>
                    {locale.code}
                  </Table.Cell>
                  {FeatureFlags.isEnabled('dictionary') && (
                    <Table.Cell>
                      {totalWords === undefined ? (
                        <Badge level={'tertiary'}>
                          {translate('pimee_enrich.entity.locale.grid.columns.dictionary_words_count.not_available')}
                        </Badge>
                      ) : (
                        translate(
                          'pimee_enrich.entity.locale.grid.columns.dictionary_words_count.label',
                          {count: `${totalWords}`},
                          totalWords
                        )
                      )}
                    </Table.Cell>
                  )}
                </Table.Row>
              );
            })}
          </Table.Body>
        </Table>
      )}
    </>
  );
};

export {LocalesEEDataGrid};
