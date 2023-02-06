import React, {useState} from 'react';
import {Dropdown, SwitcherButton} from 'akeneo-design-system';
import {NomenclatureFilter} from '../models';

type Props = {
  filter: NomenclatureFilter
  onChange: (value: NomenclatureFilter) => void
}

const values: {code: NomenclatureFilter, label: string}[] = [
  {code: 'all', label: 'All'},
  {code: 'error', label: 'Error'},
  {code: 'empty', label: 'Missing'},
  {code: 'filled', label: 'Filled'},
];

const NomenclatureValuesDisplayFilter: React.FC<Props> = ({filter, onChange}) => {
  const [isOpen, setIsOpen] = useState(false);
  const open = () => setIsOpen(true);
  const close = () => setIsOpen(false);


  return (
    <Dropdown>
      <SwitcherButton
        inline
        onClick={open}
        label={'DIsplay TODO'}
      >
        <span>
          {filter}
        </span>
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>Title TODO</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {values.map(({code, label}) => (
              <Dropdown.Item
                aria-selected={code === filter}
                key={code}
                onClick={() => {
                  close();
                  onChange(code);
                }}
                isActive={code === filter}
              >
                <span key={code}>{label} TODO</span>
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {NomenclatureValuesDisplayFilter};
