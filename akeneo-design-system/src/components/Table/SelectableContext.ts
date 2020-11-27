import {createContext} from 'react';

type SelectableContextType = {
  isSelectable: boolean;
  displayCheckbox: boolean;
};

const SelectableContext = createContext<SelectableContextType>({
  isSelectable: false,
  displayCheckbox: false,
});

export {SelectableContext};
