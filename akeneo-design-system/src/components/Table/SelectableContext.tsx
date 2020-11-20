import {createContext, useContext} from 'react';

type SelectableContextType = {
  isSelectable: boolean;
  amountSelectedRows?: number;
};

const SelectableContext = createContext<SelectableContextType>({
  isSelectable: false,
  amountSelectedRows: undefined,
});

const useSelectableContext = () => {
  const selectableContext = useContext(SelectableContext);
  if (!selectableContext) {
    throw new Error('[AttributeContext]: attribute context has not been properly initiated');
  }

  return selectableContext;
};

export {SelectableContext, useSelectableContext};
