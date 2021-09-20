import React, {FC, useCallback, useRef, useState} from 'react';
import {Table, IconButton, CloseIcon, Pagination, useAutoFocus} from 'akeneo-design-system';
import styled from 'styled-components';
import {Word} from '../../../../domain';
import {useDebounceCallback, useTranslate} from '@akeneo-pim-community/shared';
import {NoSearchResults} from './NoSearchResults';
import {NoData} from './NoData';
import {WordsSearchBar} from './WordsSearchBar';
import {useDictionaryState} from '../../../../infrastructure';

const WordsGrid: FC = () => {
  const translate = useTranslate();
  const {dictionary, totalWords, itemsPerPage, currentPage, search, deleteWord} = useDictionaryState();
  const [searchString, setSearchString] = useState('');
  const debouncedSearch = useDebounceCallback(search, 300);
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

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
            searchValue={searchString}
            onSearchChange={onSearch}
            resultNumber={totalWords}
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
                          title={translate('pim_common.remove')}
                          ghost="borderless"
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

const WordLabel = styled.span`
  text-transform: capitalize;
`;

const ActionContainer = styled.div`
  text-align: right;
  width: 100%;
`;

export {WordsGrid};
