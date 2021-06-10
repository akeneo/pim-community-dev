import {CodeLabelSelector} from './CodeLabelSelector';
import {useAttribute} from '../../../../hooks';
import React from 'react';
import {Source, Selection} from '../../../../models';

type SelectorProps = {
  source: Source;
  onSelectionChange: (selection: Selection) => void;
};

const Selector = ({source, onSelectionChange}: SelectorProps) => {
  const attribute = useAttribute(source.code);

  if (null === attribute) return null;

  switch (attribute.type) {
    case 'pim_catalog_simpleselect':
    case 'pim_catalog_multiselect':
      return <CodeLabelSelector selection={source.selection} onSelectionChange={onSelectionChange} />;

    default:
      return null;
  }
};

export {Selector};
