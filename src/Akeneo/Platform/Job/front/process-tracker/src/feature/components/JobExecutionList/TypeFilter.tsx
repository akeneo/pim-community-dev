import React from 'react';
import {Checkbox, Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate, Translate} from '@akeneo-pim-community/shared';
import {useJobExecutionTypes} from '../../hooks/useJobExecutionTypes';

const getStatusFilterValueLabel = (translate: Translate, typeFilterValue: string[]): string => {
  switch (typeFilterValue.length) {
    case 0:
      return translate('akeneo_job_process_tracker.type_filter.all');
    case 1:
      return translate(`akeneo_job_process_tracker.type_filter.${typeFilterValue[0]}`);
    default:
      return translate('pim_common.selected', {itemsCount: typeFilterValue.length}, typeFilterValue.length);
  }
};

type TypeFilterProps = {
  typeFilterValue: string[];
  onTypeFilterChange: (typeFilterValue: string[]) => void;
};

const TypeFilter = ({typeFilterValue, onTypeFilterChange}: TypeFilterProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const types = useJobExecutionTypes();

  return (
    <Dropdown>
      <SwitcherButton onClick={open} label={translate('akeneo_job_process_tracker.type_filter.label')}>
        {getStatusFilterValueLabel(translate, typeFilterValue)}
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('akeneo_job_process_tracker.type_filter.label')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            <Dropdown.Item>
              <Checkbox checked={0 === typeFilterValue.length} onChange={() => onTypeFilterChange([])} />
              {translate('akeneo_job_process_tracker.type_filter.all')}
            </Dropdown.Item>
            {types &&
              types.map(type => (
                <Dropdown.Item key={type}>
                  <Checkbox
                    checked={typeFilterValue.includes(type)}
                    onChange={checked => {
                      if (checked) {
                        onTypeFilterChange([...typeFilterValue, type]);
                      } else {
                        onTypeFilterChange(typeFilterValue.filter(typeFilterValueType => typeFilterValueType !== type));
                      }
                    }}
                  />
                  {translate(`akeneo_job_process_tracker.type_filter.${type}`)}
                </Dropdown.Item>
              ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {TypeFilter};
