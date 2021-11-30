import React from 'react';
import {AssetCollectionSelection} from '../model';

const AssetCollectionSelector = ({
  onSelectionChange,
}: {
  onSelectionChange: (updatedSelection: AssetCollectionSelection) => void;
}) => (
  <button
    onClick={() =>
      onSelectionChange({
        type: 'label',
        locale: 'en_US',
        separator: ',',
      })
    }
  >
    Asset collection selector
  </button>
);

export {AssetCollectionSelector};
