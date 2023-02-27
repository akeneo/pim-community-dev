import React from 'react';
import {NomenclatureFilter} from '../../models';

type Props = {
  filter: NomenclatureFilter;
  onChange: (value: NomenclatureFilter) => void;
};

const NomenclatureValuesDisplayFilter: React.FC<Props> = ({filter, onChange}) => {
  return (
    <>
      <span>NomenclatureValuesDisplayFilterMock</span>
      <span>Filter = {filter}</span>
      <button
        onClick={() => {
          onChange('all');
        }}
      >
        Filter with all
      </button>
      <button
        onClick={() => {
          onChange('error');
        }}
      >
        Filter with error
      </button>
      <button
        onClick={() => {
          onChange('filled');
        }}
      >
        Filter with filled
      </button>
      <button
        onClick={() => {
          onChange('empty');
        }}
      >
        Filter with empty
      </button>
    </>
  );
};

export {NomenclatureValuesDisplayFilter};
