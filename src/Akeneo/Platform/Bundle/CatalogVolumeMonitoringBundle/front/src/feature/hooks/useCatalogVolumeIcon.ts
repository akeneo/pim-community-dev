import React, {useContext} from 'react';
import {AkeneoIcon} from 'akeneo-design-system';
import {IconsMappingContext} from '../context/IconsMappingContext';

const useCatalogVolumeIcon = (name: string) => {
  const mapping = useContext(IconsMappingContext);
  return React.createElement(mapping[name] ?? AkeneoIcon, {});
};

export {useCatalogVolumeIcon};
