import React, {useEffect, useState} from 'react';

import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption} from '../model';
import {useAttributeContext} from '../contexts';
import {useAttributeOptionsListState} from '../hooks/useAttributeOptionsListState';
import {useSortedAttributeOptions} from '../hooks/useSortedAttributeOptions';
import ToggleButton from './ToggleButton';
import ListItem, {DragItem} from './ListItem';
import NewOptionPlaceholder from './NewOptionPlaceholder';

interface ListProps {
  selectAttributeOption: (selectedOptionId: number | null) => void;
  isNewOptionFormDisplayed: boolean;
  showNewOptionForm: (isDisplayed: boolean) => void;
  selectedOptionId: number | null;
  deleteAttributeOption: (attributeOptionId: number) => void;
  manuallySortAttributeOptions: (attributeOptions: AttributeOption[]) => void;
}

const List = ({
  selectAttributeOption,
  selectedOptionId,
  isNewOptionFormDisplayed,
  showNewOptionForm,
  deleteAttributeOption,
  manuallySortAttributeOptions,
}: ListProps) => {
  const {attributeOptions, extraData} = useAttributeOptionsListState();
  const translate = useTranslate();
  const attributeContext = useAttributeContext();
  const {sortedAttributeOptions, moveAttributeOption, validateMoveAttributeOption} = useSortedAttributeOptions(
    attributeOptions,
    attributeContext.autoSortOptions,
    manuallySortAttributeOptions
  );
  const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(isNewOptionFormDisplayed);
  const [dragItem, setDragItem] = useState<DragItem | null>(null);

  useEffect(() => {
    if (selectedOptionId !== null) {
      setShowNewOptionPlaceholder(false);
    }
  }, [selectedOptionId]);

  const onSelectItem = (optionId: number) => {
    setShowNewOptionPlaceholder(false);
    selectAttributeOption(optionId);
    showNewOptionForm(false);
  };

  const displayNewOptionPlaceholder = () => {
    setShowNewOptionPlaceholder(true);
    selectAttributeOption(null);
    showNewOptionForm(true);
  };

  const cancelNewOption = () => {
    showNewOptionForm(false);
    setShowNewOptionPlaceholder(false);
    if (attributeOptions !== null && attributeOptions.length > 0) {
      selectAttributeOption(attributeOptions[0].id);
    }
  };

  return (
    <div className="AknSubsection AknAttributeOption-list">
      <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
        <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}</span>
        <div
          className="AknButton AknButton--micro"
          onClick={() => displayNewOptionPlaceholder()}
          role="add-new-attribute-option-button"
        >
          {translate('pim_enrich.entity.product.module.attribute.add_option')}
        </div>
      </div>

      <label className="AknFieldContainer-header" htmlFor="auto-sort-options">
        {translate('pim_enrich.entity.attribute.property.auto_option_sorting')}
      </label>
      <ToggleButton />

      <div className="AknAttributeOption-list-optionsList" role="attribute-options-list">
        {sortedAttributeOptions !== null &&
          sortedAttributeOptions.map((attributeOption: AttributeOption, index: number) => {
            return (
              <ListItem
                key={attributeOption.code}
                data={attributeOption}
                selectAttributeOption={onSelectItem}
                isSelected={selectedOptionId === attributeOption.id}
                deleteAttributeOption={deleteAttributeOption}
                moveAttributeOption={moveAttributeOption}
                validateMoveAttributeOption={validateMoveAttributeOption}
                dragItem={dragItem}
                setDragItem={setDragItem}
                index={index}
              >
                {extraData[attributeOption.code]}
              </ListItem>
            );
          })}

        {showNewOptionPlaceholder && <NewOptionPlaceholder cancelNewOption={cancelNewOption} />}
      </div>
    </div>
  );
};

export default List;
