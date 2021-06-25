import React, {useState} from 'react';
import {
  Button,
  Dropdown,
  getColor,
  GroupsIllustration,
  Search,
  useBooleanState,
  useDebounce,
  usePaginatedResults,
} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useAvailableSourcesFetcher} from '../../../hooks/useAvailableSourcesFetcher';
import {AvailableSourceGroup} from '../../../models';
import {flattenSections} from './flattenSections';

const NoResultsContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  line-height: normal;
  margin: 10px 0 24px 0;
  color: ${getColor('grey', 140)};
`;

type AddSourceDropdownProps = {
  onSourceSelected: (sourceCode: string, sourceType: string) => void;
};

const AddSourceDropdown = ({onSourceSelected}: AddSourceDropdownProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const [searchValue, setSearchValue] = useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue);
  const userContext = useUserContext();
  const fetchSources = useAvailableSourcesFetcher(debouncedSearchValue, userContext.get('catalogLocale'));
  const [items, handleNextPage] = usePaginatedResults<AvailableSourceGroup>(
    page => fetchSources(page),
    [debouncedSearchValue],
    isOpen
  );

  return (
    <Dropdown>
      <Button size="small" ghost={true} level="tertiary" onClick={open}>
        {translate('akeneo.tailored_export.column_details.sources.add')}
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Search
              onSearchChange={setSearchValue}
              placeholder={translate('pim_common.search')}
              searchValue={searchValue}
              title={translate('pim_common.search')}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection onNextPage={handleNextPage}>
            {flattenSections(items).map((item, index) =>
              'section' === item.type ? (
                <Dropdown.Section key={`section_${item.code}_${index}`}>{item.label}</Dropdown.Section>
              ) : (
                <Dropdown.Item
                  key={`source_${item.code}_${index}`}
                  onClick={() => {
                    onSourceSelected(item.code, item.sourceType);
                    setSearchValue('');
                    close();
                  }}
                >
                  {item.label}
                </Dropdown.Item>
              )
            )}
            {0 === items.length && (
              <NoResultsContainer>
                <GroupsIllustration size={128} />
                {translate('akeneo.tailored_export.column_details.sources.no_result')}
              </NoResultsContainer>
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddSourceDropdown};
