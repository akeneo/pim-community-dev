import React, {useCallback} from 'react';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {NomenclatureFilter} from '../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  filter: NomenclatureFilter;
  onChange: (value: NomenclatureFilter) => void;
};

const FILTERS: NomenclatureFilter[] = ['all', 'error', 'empty', 'filled'];

const NomenclatureValuesDisplayFilter: React.FC<Props> = ({filter, onChange}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();

  const getLabel = useCallback(
    codeFilter => translate(`pim_identifier_generator.nomenclature.filters.${codeFilter}`),
    [translate]
  );

  return (
    <Dropdown>
      <SwitcherButton inline onClick={open} label={translate('pim_identifier_generator.nomenclature.display')}>
        <span>{getLabel(filter)}</span>
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_identifier_generator.nomenclature.operator_placeholder')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {FILTERS.map(myFilter => (
              <Dropdown.Item
                aria-selected={myFilter === filter}
                key={myFilter}
                onClick={() => {
                  close();
                  onChange(myFilter);
                }}
                isActive={myFilter === filter}
              >
                <span>{getLabel(myFilter)}</span>
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {NomenclatureValuesDisplayFilter};
