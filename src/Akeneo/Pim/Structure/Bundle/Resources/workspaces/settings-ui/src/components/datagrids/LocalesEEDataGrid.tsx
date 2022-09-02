import React, {FC, useEffect, useRef, useState} from 'react';
import {FollowLocaleHandler, Locale, NoResults, useFilteredLocales} from '@akeneo-pim-community/settings-ui';
import {useDebounceCallback, useTranslate} from '@akeneo-pim-community/shared';
import {Badge, Search, Table, useAutoFocus} from 'akeneo-design-system';
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
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  useEffect(() => {
    updateTotalLocalesCount(filteredLocales.length);
  }, [filteredLocales]);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  const localeColumnWidth = FeatureFlags.isEnabled('data_quality_insights_all_criteria') ? '380px' : undefined;

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
        <LocalesTable isDqiFeatureActive={FeatureFlags.isEnabled('data_quality_insights_all_criteria')}>
          <Table
            className={'grid'}
            isSelectable={FeatureFlags.isEnabled('data_quality_insights_all_criteria')}
            displayCheckbox={!!selectionState}
          >
            <Table.Header>
              {/* @ts-ignore | @fixme: width props definition */}
              <Table.HeaderCell width={localeColumnWidth}>
                {translate('pim_enrich.entity.locale.grid.columns.code')}
              </Table.HeaderCell>
              {FeatureFlags.isEnabled('data_quality_insights_all_criteria') && (
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
                    {FeatureFlags.isEnabled('data_quality_insights_all_criteria') && (
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

const LocalesSearchBar = styled(Search)`
  margin: 10px 40px 20px 40px;
`;

const LocalesTable = styled.div<{isDqiFeatureActive: boolean}>`
  margin-right: 40px;
  margin-left: ${({isDqiFeatureActive}) => (isDqiFeatureActive ? '0' : '40px')};
`;

export {LocalesEEDataGrid};
