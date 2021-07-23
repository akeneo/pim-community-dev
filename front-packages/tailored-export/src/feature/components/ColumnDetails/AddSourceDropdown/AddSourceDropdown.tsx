import React, {useState} from 'react';
import styled from 'styled-components';
import {
  Button,
  Dropdown,
  getColor,
  GroupsIllustration,
  Search,
  useBooleanState,
  useDebounce,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {flattenSections} from './flattenSections';
import {useOffsetAvailableSources} from '../../../hooks';
import {MAX_SOURCE_COUNT} from '../../../models';

const NoResultsContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  line-height: normal;
  margin: 10px 0 24px 0;
  color: ${getColor('grey', 140)};
`;

type AddSourceDropdownProps = {
  canAddSource: boolean;
  onSourceSelected: (sourceCode: string, sourceType: string) => void;
};

const AddSourceDropdown = ({canAddSource, onSourceSelected}: AddSourceDropdownProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const [searchValue, setSearchValue] = useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue);
  const [items, handleNextPage] = useOffsetAvailableSources(debouncedSearchValue, isOpen);

  const handleClose = () => {
    close();
    setSearchValue('');
  };

  return (
    <Dropdown>
      <Button
        size="small"
        ghost={true}
        level="tertiary"
        title={
          !canAddSource
            ? translate('akeneo.tailored_export.validation.sources.max_source_count_reached', {limit: MAX_SOURCE_COUNT})
            : undefined
        }
        disabled={!canAddSource}
        onClick={open}
      >
        {translate('akeneo.tailored_export.column_details.sources.add')}
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={handleClose}>
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
                    handleClose();
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
