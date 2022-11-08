import React, {useMemo, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search, useBooleanState, useDebounce} from 'akeneo-design-system';
import {PROPERTY_NAMES} from '../../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type PropertiesSelection = {
  code: string;
  items: {
    code: string;
  }[];
};

const AddPropertyButton: React.FC = () => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue);

  const addElement = () => {
    open();
  };

  const onSearchClose = () => {
    close();
    setSearchValue('');
  };

  const items = useMemo(
    (): PropertiesSelection[] => [
      {
        code: 'system',
        items: [
          {
            code: PROPERTY_NAMES.FREE_TEXT,
          },
        ],
      },
    ],
    [translate]
  );

  const filterElements = useMemo((): PropertiesSelection[] => {
    if ('' !== debouncedSearchValue) {
      return items
        .map(item => {
          const filteredItems = item.items.filter(subItem =>
            subItem.code.toLowerCase().includes(debouncedSearchValue.toLowerCase())
          );
          return {...item, items: filteredItems};
        })
        .filter(item => item.items.length > 0);
    } else {
      return items;
    }
  }, [debouncedSearchValue, items]);

  return (
    <Dropdown>
      <Button active ghost level="secondary" onClick={addElement}>
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
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection
            noResultIllustration={React.createElement(GroupsIllustration)}
            noResultTitle={translate('pim_common.no_search_result')}
          >
            {filterElements.map(({code, items}) => (
              <React.Fragment key={code}>
                <Dropdown.Section>
                  {translate(`pim_identifier_generator.structure.property_type.section.${code}`)}
                </Dropdown.Section>
                {items.map(({code}) => (
                  <Dropdown.Item key={code}>
                    {translate(`pim_identifier_generator.structure.property_type.${code}`)}
                  </Dropdown.Item>
                ))}
              </React.Fragment>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddPropertyButton};
