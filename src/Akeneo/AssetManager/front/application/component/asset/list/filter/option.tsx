import React, {memo} from 'react';
import styled from 'styled-components';
import {Dropdown, MultiSelectInput, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate, getLabel} from '@akeneo-pim-community/shared';
import {FilterView, FilterViewProps} from 'akeneoassetmanager/application/configuration/value';
import {isOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {isOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';
import OptionCode from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';

const DropdownContent = styled.div`
  padding: 5px 20px;
`;

type OptionFilterViewProps = FilterViewProps & {
  context: {
    locale: string;
  };
};

const DEFAULT_OPERATOR = 'IN';

const OptionFilterView: FilterView = memo(({attribute, filter, onFilterUpdated, context}: OptionFilterViewProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();

  if (!(isOptionAttribute(attribute) || isOptionCollectionAttribute(attribute))) {
    return null;
  }

  const value = undefined !== filter ? filter.value : [];
  const labels = value.map((optionCode: OptionCode) => {
    const option = attribute.options.find(({code}) => code === optionCode) ?? null;

    return null === option ? `[${optionCode}]` : getOptionLabel(option, context.locale);
  });
  const attributeLabel = getLabel(attribute.labels, context.locale, attribute.code);

  return (
    <Dropdown>
      <SwitcherButton inline={false} label={attributeLabel} onClick={open}>
        {0 === labels.length ? translate('pim_asset_manager.asset.grid.filter.option.all') : labels.join(', ')}
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{attributeLabel}</Dropdown.Title>
          </Dropdown.Header>
          <DropdownContent>
            <MultiSelectInput
              openLabel={translate('pim_common.open')}
              emptyResultLabel={translate('pim_common.no_result')}
              removeLabel={translate('pim_common.remove')}
              value={value}
              onChange={optionCodes =>
                onFilterUpdated({
                  field: getAttributeFilterKey(attribute),
                  operator: DEFAULT_OPERATOR,
                  value: optionCodes,
                  context: {},
                })
              }
            >
              {attribute.options.map(option => (
                <MultiSelectInput.Option key={option.code} value={option.code}>
                  {getOptionLabel(option, context.locale)}
                </MultiSelectInput.Option>
              ))}
            </MultiSelectInput>
          </DropdownContent>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
});

export const filter = OptionFilterView;
