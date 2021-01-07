import React, {FC, useState} from 'react';
import {Table, IconButton, CloseIcon, Pagination} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {Word} from '../../../../domain';
import {SearchBar} from '@akeneo-pim-community/shared';
import {NoSearchResults} from './NoSearchResults';
import {NoData} from './NoData';

type WordsGridProps = {
  words: Word[];
};

const WordsGrid: FC<WordsGridProps> = ({words}) => {
  const translate = useTranslate();
  const [page, setPage] = useState<number>(1);
  const searchString = '';

  return (
    <>
      <>
        {words.length && searchString === '' ?
          <WordsSearchBar
            count={words.length}
            searchValue={searchString}
            placeholder={translate('akeneo_data_quality_insights.dictionary.searchPlaceholder')}
            onSearchChange={() => console.log('search')}
          /> :
          <></>
        }
      </>

      <Pagination onClick={setPage} currentPage={page} itemsTotal={words.length} itemsPerPage={1}/>

      <>
        {words.length === 0 ?
          (searchString !== '' ? <NoSearchResults/> : <NoData/>) :
          <Table>
            <Table.Header>
              <Table.HeaderCell>
                {translate('akeneo_data_quality_insights.dictionary.words')}
              </Table.HeaderCell>
              <Table.HeaderCell/>
            </Table.Header>
            <Table.Body>
              {words.map((word: Word) => {
                return (
                  <Table.Row key={`word-${word.id}`} onClick={() => console.log('test')}>
                    <Table.Cell rowTitle={true}>
                      <WordLabel>{word.label}</WordLabel>
                    </Table.Cell>
                    <Table.ActionCell>
                      <ActionContainer>
                        <IconButton icon={<CloseIcon/>} title={''} ghost={"borderless"} level="tertiary" size="small"/>
                      </ActionContainer>
                    </Table.ActionCell>
                  </Table.Row>
                )
              })}
            </Table.Body>
          </Table>
        }
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
