import React, {FC, useCallback, useState} from 'react';
import {Table, IconButton, CloseIcon, Pagination} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {Word} from '../../../../domain';
import {SearchBar, useDebounceCallback} from '@akeneo-pim-community/shared';
import {NoSearchResults} from './NoSearchResults';
import {NoData} from './NoData';
import {useDictionaryState} from '../../../../infrastructure';

const WordsGrid: FC = () => {
  const translate = useTranslate();
  const {dictionary, totalWords, itemsPerPage, currentPage, search, deleteWord} = useDictionaryState();
  const [searchString, setSearchString] = useState('');
  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue, 1);
  };

  const onChangePage = useCallback(
    (pageNumber: number) => {
      search(searchString, pageNumber);
    },
    [searchString, search]
  );

  const onDeleteWord = async (wordId: number) => {
    await deleteWord(wordId);
    let pageToRedirect = currentPage;
    if (dictionary !== null) {
      if (Object.keys(dictionary).length === 1) {
        pageToRedirect = Math.max(1, currentPage - 1);
      }
    }
    search(searchString, pageToRedirect);
  };

  if (dictionary === null) {
    return <></>;
  }

  return (
    <>
      {totalWords > 0 || searchString !== '' ? (
        <>
          <WordsSearchBar
            count={totalWords}
            searchValue={searchString}
            placeholder={translate('akeneo_data_quality_insights.dictionary.searchPlaceholder')}
            onSearchChange={onSearch}
            className={'filter-box'}
          />
          <Pagination
            followPage={onChangePage}
            currentPage={totalWords > 0 ? currentPage : 0}
            totalItems={totalWords}
            itemsPerPage={itemsPerPage}
          />
        </>
      ) : (
        <></>
      )}

      <>
        {Object.keys(dictionary).length === 0 ? (
          searchString !== '' ? (
            <NoSearchResults />
          ) : (
            <NoData />
          )
        ) : (
          <Table>
            <Table.Header>
              <Table.HeaderCell>{translate('akeneo_data_quality_insights.dictionary.words')}</Table.HeaderCell>
              <Table.HeaderCell />
            </Table.Header>
            <Table.Body>
              {Object.values(dictionary).map((word: Word) => {
                return (
                  <Table.Row key={`word-${word.id}`}>
                    <Table.Cell rowTitle={true}>
                      <WordLabel>{word.label}</WordLabel>
                    </Table.Cell>
                    <Table.ActionCell>
                      <ActionContainer>
                        <IconButton
                          icon={<CloseIcon />}
                          title={''}
                          ghost={'borderless'}
                          level="tertiary"
                          size="small"
                          onClick={() => onDeleteWord(word.id)}
                        />
                      </ActionContainer>
                    </Table.ActionCell>
                  </Table.Row>
                );
              })}
            </Table.Body>
          </Table>
        )}
      </>
    </>
  );
};

const WordsSearchBar = styled(SearchBar)`
  margin: 10px 0 20px;
`;

const WordLabel = styled.span`
  text-transform: capitalize;
  font-weight: bold;
`;

const ActionContainer = styled.div`
  text-align: right;
  width: 100%;
`;

export {WordsGrid};
