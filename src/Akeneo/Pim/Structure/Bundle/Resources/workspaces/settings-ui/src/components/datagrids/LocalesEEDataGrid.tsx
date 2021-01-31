import React, {FC, useEffect, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {FollowLocaleHandler, Locale, NoResults, useFilteredLocales} from '@akeneo-pim-community/settings-ui';
import {SearchBar, useDebounceCallback} from '@akeneo-pim-community/shared';
import {Badge, Table, getColor} from 'akeneo-design-system';
import styled from 'styled-components';
import {useLocaleSelection} from '../../hooks/locales/useLocaleSelection';

const FeatureFlags = require('pim/feature-flags');

type Props = {
  locales: Locale[];
  followLocale?: FollowLocaleHandler;
  getDictionaryTotalWords: (localeCode: string) => number | undefined;
};

const LocalesEEDataGrid: FC<Props> = ({locales, followLocale, getDictionaryTotalWords}) => {
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const {filteredLocales, search} = useFilteredLocales(locales);
  const {isItemSelected, onSelectionChange, selectionState, updateTotalLocalesCount} = useLocaleSelection();

  const debouncedSearch = useDebounceCallback(search, 300);

  useEffect(() => {
    updateTotalLocalesCount(filteredLocales.length);
  }, [filteredLocales]);

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
        <LocalesTable>
          <Table
            className={'grid'}
            isSelectable={FeatureFlags.isEnabled('dictionary')}
            displayCheckbox={!!selectionState}
          >
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
                const totalWords = getDictionaryTotalWords(locale.code);

                return (
                  <Table.Row
                    key={locale.code}
                    onClick={followLocale !== undefined ? () => followLocale(locale) : undefined}
                    onSelectToggle={value => onSelectionChange(locale.code, value)}
                    isSelected={isItemSelected(locale.code)}
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
        </LocalesTable>
      )}
    </>
  );
};

const LocalesSearchBar = styled(SearchBar)`
  margin: 10px 40px 20px 0px;
  border: none;
  border-left: 40px solid ${getColor('white')};

  :after {
    content: '';
    background-color: ${getColor('grey', 100)};
    height: 1px;
    bottom: 1px;
    position: absolute;
    width: 100%;
  }
`;

const LocalesTable = styled.div`
  margin-right: 40px;
`;

export {LocalesEEDataGrid};
