import React, {useMemo, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search, useBooleanState, useDebounce} from 'akeneo-design-system';
import {
  ATTRIBUTE_TYPE,
  AttributeType,
  Property,
  PROPERTY_NAMES,
  RefEntityProperty,
  SimpleSelectProperty,
  Structure
} from '../../models';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useGetPropertyItems} from '../../hooks';

type AddPropertyButtonProps = {
  onAddProperty: (property: Property) => void;
  structure: Structure;
};

type FlatItemsGroup = {
  id: string;
  text: string;
  isSection: boolean;
  isVisible?: boolean;
  type?: AttributeType;
};

const AddPropertyButton: React.FC<AddPropertyButtonProps> = ({onAddProperty, structure}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue);
  const {data, fetchNextPage} = useGetPropertyItems(debouncedSearchValue, isOpen);

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

  const addProperty = (id: string, type?: AttributeType) => {
    if (id === PROPERTY_NAMES.FREE_TEXT) {
      handleAddProperty({type: PROPERTY_NAMES.FREE_TEXT, string: ''});
    } else if (id === PROPERTY_NAMES.AUTO_NUMBER) {
      handleAddProperty({type: PROPERTY_NAMES.AUTO_NUMBER, digitsMin: 1, numberMin: 1});
    } else if (id === PROPERTY_NAMES.FAMILY) {
      handleAddProperty({
        type: PROPERTY_NAMES.FAMILY,
        process: {
          type: null,
        },
      });
    } else if (type === ATTRIBUTE_TYPE.SIMPLE_SELECT) {
      handleAddProperty({
        type: PROPERTY_NAMES.SIMPLE_SELECT,
        attributeCode: id,
        process: {
          type: null,
        },
      } as SimpleSelectProperty);
    } else if (type === ATTRIBUTE_TYPE.REF_ENTITY) {
      handleAddProperty({
        type: PROPERTY_NAMES.REF_ENTITY,
        attributeCode: id,
        process: {
          type: null,
        },
      } as RefEntityProperty);
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
      if (tab.findIndex((section: FlatItemsGroup) => section.isSection && section.id === item.id) === -1) {
        tab.push({...item, isSection: true});
      }
      item.children.forEach(child =>
        tab.push({
          ...child,
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
            {flatItems?.map(({id, text, isSection, isVisible, type}) =>
              isSection ? (
                <Dropdown.Section key={`section-${id}`}>{text}</Dropdown.Section>
              ) : (
                isVisible && (
                  <Dropdown.Item key={id} onClick={() => addProperty(id, type)}>
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
