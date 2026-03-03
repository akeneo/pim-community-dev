import React from 'react';
import {Button, Dropdown, GroupsIllustration, Search, useBooleanState} from 'akeneo-design-system';
import {ATTRIBUTE_TYPE, AttributeType, Condition, CONDITION_NAMES, Operator} from '../../models';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useGetConditionItems} from '../../hooks';

type AddConditionButtonProps = {
  onAddCondition: (condition: Condition) => void;
  conditions: Condition[];
};

export const MAX_CONDITIONS_COUNT = 10;

const AddConditionButton: React.FC<AddConditionButtonProps> = ({conditions, onAddCondition}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);

  const {conditionItems, handleNextPage, searchValue, setSearchValue} = useGetConditionItems(isOpen, conditions);

  const addElement = () => {
    open();
  };

  const onSearchClose = () => {
    close();
    setSearchValue('');
  };

  const addCondition = (id: string, type?: AttributeType) => {
    if (id === 'family') {
      onAddCondition({type: CONDITION_NAMES.FAMILY, operator: Operator.IN, value: []});
      close();
    } else if (id === 'enabled') {
      onAddCondition({type: CONDITION_NAMES.ENABLED});
      close();
    } else if (id === 'categories') {
      onAddCondition({type: CONDITION_NAMES.CATEGORIES, operator: Operator.IN, value: []});
      close();
    } else if (type === ATTRIBUTE_TYPE.SIMPLE_SELECT) {
      onAddCondition({type: CONDITION_NAMES.SIMPLE_SELECT, operator: Operator.IN, value: [], attributeCode: id});
      close();
    } else if (type === ATTRIBUTE_TYPE.MULTI_SELECT) {
      onAddCondition({type: CONDITION_NAMES.MULTI_SELECT, operator: Operator.IN, value: [], attributeCode: id});
      close();
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

  const flatItems: {id: string; text: string; onClick: (() => void) | undefined}[] = [];
  conditionItems.forEach(({id, text, children}) => {
    flatItems.push({id: `section-${id}`, text, onClick: undefined});
    children.forEach(({id, text, type}) => {
      flatItems.push({id: `item-${id}`, text, onClick: () => addCondition(id, type)});
    });
  });

  return (
    <Dropdown>
      {conditions.length < MAX_CONDITIONS_COUNT && (
        <Button active ghost level="secondary" onClick={addElement} size="small">
          {translate('pim_identifier_generator.structure.add_element')}
        </Button>
      )}
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
            onNextPage={handleNextPage}
          >
            {flatItems.map(({id, text, onClick}) =>
              onClick ? (
                <Dropdown.Item key={id} onClick={onClick}>
                  {text}
                </Dropdown.Item>
              ) : (
                <Dropdown.Section key={id}>{text}</Dropdown.Section>
              )
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddConditionButton};
