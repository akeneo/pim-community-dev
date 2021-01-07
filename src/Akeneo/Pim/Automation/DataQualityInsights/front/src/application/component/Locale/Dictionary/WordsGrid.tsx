import React, {FC} from 'react';
import {Table, IconButton, CloseIcon, Pagination} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {Word} from '../../../../domain';
import {SearchBar} from '@akeneo-pim-community/shared';
import {NoSearchResults} from './NoSearchResults';
import {NoData} from './NoData';
import {useDictionaryState} from '../../../../infrastructure';

const WordsGrid: FC = () => {
  const translate = useTranslate();
  const {dictionary, totalWords, itemsPerPage, setCurrentPage, currentPage} = useDictionaryState();

  const searchString = '';

  if (dictionary === null) {
    return <></>;
  }

  return (
    <>
      <>
        {totalWords > 0 ?
          <>
            <WordsSearchBar
              count={Object.keys(dictionary).length}
              searchValue={searchString}
              placeholder={translate('akeneo_data_quality_insights.dictionary.searchPlaceholder')}
              onSearchChange={() => console.log('search')}
            />
            <Pagination onClick={setCurrentPage} currentPage={currentPage} totalItems={totalWords} itemsPerPage={itemsPerPage}/>
          </>:
          <></>
        }
      </>

      <>
        {Object.keys(dictionary).length === 0 ?
          (searchString !== '' ? <NoSearchResults/> : <NoData/>) :
          <Table>
            <Table.Header>
              <Table.HeaderCell>
                {translate('akeneo_data_quality_insights.dictionary.words')}
              </Table.HeaderCell>
              <Table.HeaderCell/>
            </Table.Header>
            <Table.Body>
              {Object.values(dictionary).map((word: Word) => {
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
