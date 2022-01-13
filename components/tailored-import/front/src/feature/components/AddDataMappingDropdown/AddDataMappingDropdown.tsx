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
import {useOffsetAvailableTargets} from '../../hooks';
import {createDataMapping, DataMapping, MAX_DATA_MAPPING_COUNT} from '../../models';

type AddDataMappingDropdownProps = {
  canAddDataMapping: boolean;
  onDataMappingAdded: (dataMapping: DataMapping) => void;
};

const AddDataMappingDropdown = ({canAddDataMapping, onDataMappingAdded}: AddDataMappingDropdownProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const [searchValue, setSearchValue] = useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue);
  const [items, handleNextPage] = useOffsetAvailableTargets(debouncedSearchValue, isOpen);
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);

  const handleTargetSelected = (targetCode: string, targetType: string): void => {
    const dataMapping = createDataMapping(targetCode, targetType);
    onDataMappingAdded(dataMapping);
    handleClose();
  };

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
        level="secondary"
        title={
          !canAddDataMapping
            ? translate('akeneo.tailored_import.validation.data_mappings.max_data_mapping_count_reached', {
                limit: MAX_DATA_MAPPING_COUNT,
              })
            : undefined
        }
        disabled={!canAddDataMapping}
        onClick={open}
      >
        {translate('akeneo.tailored_import.add_data_mapping.target.add')} <ArrowDownIcon />
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
            noResultTitle={translate('pim_common.no_result')}
            noResultIllustration={<GroupsIllustration />}
            onNextPage={handleNextPage}
          >
            {flattenSections(items).map((item, index) =>
              'section' === item.type ? (
                <Dropdown.Section key={`section_${item.code}_${index}`}>{item.label}</Dropdown.Section>
              ) : (
                <Dropdown.Item
                  key={`target_${item.code}_${index}`}
                  onClick={() => handleTargetSelected(item.code, item.targetType)}
                >
                  {item.label}
                </Dropdown.Item>
              )
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export type {AddDataMappingDropdownProps};
export {AddDataMappingDropdown};
