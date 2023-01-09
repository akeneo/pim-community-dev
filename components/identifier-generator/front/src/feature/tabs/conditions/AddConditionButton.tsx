import React, {useMemo, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search, useBooleanState, useDebounce} from 'akeneo-design-system';
import {Condition, CONDITION_NAMES, Operator} from '../../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type ConditionsSelection = {
  code: string;
  items: {
    code: string;
    defaultValue: Condition;
    limit?: number;
  }[];
};

type AddConditionButtonProps = {
  onAddCondition: (condition: Condition) => void;
  conditions: Condition[];
};

const items: ConditionsSelection[] = [
  {
    code: 'system',
    items: [
      {
        code: CONDITION_NAMES.ENABLED,
        defaultValue: {type: CONDITION_NAMES.ENABLED},
        limit: 1,
      },
      {
        code: CONDITION_NAMES.FAMILY,
        defaultValue: {type: CONDITION_NAMES.FAMILY, operator: Operator.IN, value: []},
        limit: 1,
      },
      {
        code: CONDITION_NAMES.SELECT_OPTION,
        defaultValue: {type: CONDITION_NAMES.SELECT_OPTION, operator: Operator.IN, value: [], attributeCode: 'brand'},
        limit: 1,
      },
    ],
  },
];

const AddConditionButton: React.FC<AddConditionButtonProps> = ({conditions, onAddCondition}) => {
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

  const addCondition = (defaultValue: Condition) => {
    onAddCondition(defaultValue);
    close();
  };

  const filterElements = useMemo((): ConditionsSelection[] => {
    return items
      .map(item => {
        const filteredItems = item.items
          .filter(subItem => subItem.code.toLowerCase().includes(debouncedSearchValue.toLowerCase()))
          .filter(
            subItem =>
              typeof subItem.limit !== 'undefined' &&
              conditions.filter(condition => condition.type === subItem.code).length < subItem.limit
          );
        return {...item, items: filteredItems};
      })
      .filter(item => item.items.length > 0);
  }, [debouncedSearchValue, conditions]);

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
          >
            {filterElements.map(({code, items}) => (
              <React.Fragment key={code}>
                <Dropdown.Section>
                  {translate(`pim_identifier_generator.selection.property_type.sections.${code}`)}
                </Dropdown.Section>
                {items.map(({code, defaultValue}) => (
                  <Dropdown.Item key={code} onClick={() => addCondition(defaultValue)}>
                    {translate(`pim_identifier_generator.selection.property_type.${code}`)}
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

export {AddConditionButton};
