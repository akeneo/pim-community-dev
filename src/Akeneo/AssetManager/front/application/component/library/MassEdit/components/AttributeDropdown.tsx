import React from 'react';
import {ArrowDownIcon, Button, Dropdown, useBooleanState} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

type AddAttributeDropdownProps = {
  attributes: NormalizedAttribute[];
  locale: string;
  alreadyUsed: string[];
  onAdd: (attribute: NormalizedAttribute) => void;
};

const AddAttributeDropdown = ({attributes, locale, alreadyUsed, onAdd}: AddAttributeDropdownProps) => {
  const [isOpen, open, close] = useBooleanState(false);
  const translate = useTranslate();

  return (
    <Dropdown>
      <Button size="small" level="tertiary" ghost onClick={open}>
        {translate('Add attributes')} <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('Attributes')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {attributes
              .filter(attribute => !attribute.is_read_only)
              .map(attribute => {
                return (
                  <Dropdown.Item
                    key={attribute.identifier}
                    onClick={() => {
                      onAdd(attribute);
                      close();
                    }}
                  >
                    {getLabel(attribute.labels, locale, attribute.code)}{' '}
                    {alreadyUsed.includes(attribute.identifier) && 'already used'}
                  </Dropdown.Item>
                );
              })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddAttributeDropdown};
