import React, {useEffect, useRef, useState} from 'react';
import {
  ArrowDownIcon,
  Button,
  Dropdown,
  GroupsIllustration,
  Search,
  useAutoFocus,
  useBooleanState,
  useDebounce,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {flattenSections} from './flattenSections';
import {useOffsetAvailableSources} from '../../../hooks';
import {MAX_SOURCE_COUNT} from '../../../models';

type AddSourceDropdownProps = {
  canAddSource: boolean;
  type: string;
  onSourceSelected: (sourceCode: string, sourceType: string) => void;
};

const AddSourceDropdown = ({canAddSource, type, onSourceSelected}: AddSourceDropdownProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const [searchValue, setSearchValue] = useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue);
  const [items, handleNextPage] = useOffsetAvailableSources(debouncedSearchValue, type, isOpen);
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);

  const handleClose = () => {
    close();
    setSearchValue('');
  };

  useEffect(() => {
    isOpen && focus();
  }, [isOpen, focus]);

  return (
    <Dropdown>
      <Button
        size="small"
        ghost={true}
        level="tertiary"
        title={
          !canAddSource
            ? translate('akeneo.syndication.validation.sources.max_source_count_reached', {limit: MAX_SOURCE_COUNT})
            : undefined
        }
        disabled={!canAddSource}
        onClick={open}
      >
        {translate('akeneo.syndication.data_mapping_details.sources.add')} <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={handleClose}>
          <Dropdown.Header>
            <Search
              inputRef={inputRef}
              onSearchChange={setSearchValue}
              placeholder={translate('pim_common.search')}
              searchValue={searchValue}
              title={translate('pim_common.search')}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection
            noResultTitle={translate('akeneo.syndication.data_mapping_details.sources.no_result')}
            noResultIllustration={<GroupsIllustration />}
            onNextPage={handleNextPage}
          >
            {flattenSections(items).map((item, index) =>
              'section' === item.type ? (
                <Dropdown.Section key={`section_${item.code}_${index}`}>{translate(item.label)}</Dropdown.Section>
              ) : (
                <Dropdown.Item
                  key={`source_${item.code}_${index}`}
                  onClick={() => {
                    onSourceSelected(item.code, item.sourceType);
                    handleClose();
                  }}
                >
                  {translate(item.label)}
                </Dropdown.Item>
              )
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddSourceDropdown};
