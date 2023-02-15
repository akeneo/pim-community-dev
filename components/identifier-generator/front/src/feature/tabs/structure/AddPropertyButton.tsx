import React, {useMemo, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search, useBooleanState, useDebounce} from 'akeneo-design-system';
import {Property, PROPERTY_NAMES, Structure} from '../../models';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useGetPropertyItems} from '../../hooks/useGetPropertyItems';
import {getAttributeByCode} from '../../hooks/useGetAttributeByCode';

type PropertiesSelection = {
  code: string;
  items: {
    code: string;
    defaultValue: Property;
    isVisible?: boolean;
  }[];
};

type AddPropertyButtonProps = {
  onAddProperty: (property: Property) => void;
  structure: Structure;
};

type FlatItemsGroup = {
  id: string;
  text: string;
  isSection: boolean;
  isVisible?: boolean;
};

const defaultValueByAttributeType = {
  [PROPERTY_NAMES.FREE_TEXT]: {type: PROPERTY_NAMES.FREE_TEXT, string: ''},
  [PROPERTY_NAMES.AUTO_NUMBER]: {type: PROPERTY_NAMES.AUTO_NUMBER, digitsMin: 1, numberMin: 1},
  [PROPERTY_NAMES.FAMILY]: {
    type: PROPERTY_NAMES.FAMILY,
    process: {
      type: null,
    },
  },
  pim_catalog_simpleselect: {
    type: PROPERTY_NAMES.SIMPLE_SELECT,
    operator: null,
    value: null,
  },
};

const AddPropertyButton: React.FC<AddPropertyButtonProps> = ({onAddProperty, structure}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue);
  const {data, fetchNextPage} = useGetPropertyItems(debouncedSearchValue, isOpen);
  const router = useRouter();

  const showAutoNumber = useMemo(() => !structure.find(({type}) => type === PROPERTY_NAMES.AUTO_NUMBER), [structure]);

  const addElement = () => {
    open();
  };

  const onSearchClose = () => {
    close();
    setSearchValue('');
  };

  const handleAddProperty = (defaultValue: Property) => {
    onAddProperty(defaultValue);
    close();
    setSearchValue('');
  };

  const addProperty = (id: string) => {
    const defaultValue = defaultValueByAttributeType[id];
    if (!defaultValue) {
      getAttributeByCode(id, router).then(response => {
        const defaultValue = defaultValueByAttributeType[response.type];
        handleAddProperty(defaultValue);
      });
    } else {
      handleAddProperty(defaultValue);
    }
  };

  const searchInputRef = React.useRef<HTMLInputElement | null>(null);
  // We can not use the useAutoFocus here because the element is hidden when dropdown is not open
  const focusCallback = React.useCallback(() => {
    if (isOpen) {
      setTimeout(() => {
        if (searchInputRef.current !== null) searchInputRef.current.focus();
      }, 0);
    }
  }, [searchInputRef, isOpen]);

  React.useEffect(focusCallback, [isOpen, focusCallback]);

  const flatItems = useMemo(() => {
    const visibilityConditions = {
      [PROPERTY_NAMES.AUTO_NUMBER]: showAutoNumber,
    };
    const tab: FlatItemsGroup[] = [];
    data?.forEach(item => {
      tab.push({id: item.id, text: item.text, isSection: true});
      item.children.forEach(child =>
        tab.push({
          id: child.id,
          text: child.text,
          isSection: false,
          isVisible: visibilityConditions[child.id] !== undefined ? visibilityConditions[child.id] : true,
        })
      );
    });
    return tab;
  }, [data, showAutoNumber]);

  return (
    <Dropdown>
      <Button active ghost level="secondary" onClick={addElement} size="small">
        {translate('pim_identifier_generator.structure.add_element')}
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={onSearchClose}>
          <Dropdown.Header>
            <Search
              onSearchChange={setSearchValue}
              placeholder={translate('pim_common.search')}
              searchValue={searchValue}
              title={translate('pim_common.search')}
              inputRef={searchInputRef}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection
            noResultIllustration={React.createElement(GroupsIllustration)}
            noResultTitle={translate('pim_common.no_search_result')}
            onNextPage={fetchNextPage}
          >
            {flatItems?.map(({id, text, isSection, isVisible}) =>
              isSection ? (
                <Dropdown.Section key={`section-${id}`}>{text}</Dropdown.Section>
              ) : (
                isVisible && (
                  <Dropdown.Item key={id} onClick={() => addProperty(id)}>
                    {text}
                  </Dropdown.Item>
                )
              )
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddPropertyButton};
