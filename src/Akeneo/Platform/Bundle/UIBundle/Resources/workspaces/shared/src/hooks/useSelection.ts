import {useState, useCallback} from 'react';

type Selection<Type = string> = {
  mode: 'in' | 'not_in';
  collection: Type[];
};

const selectAll = <Type>(selection: Selection<Type>): Selection<Type> => ({
  ...selection,
  mode: 'not_in',
  collection: [],
});
const unselectAll = <Type>(selection: Selection<Type>): Selection<Type> => ({
  ...selection,
  mode: 'in',
  collection: [],
});

const select = <Type>(selection: Selection<Type>, elementToSelect: Type): Selection<Type> => ({
  ...selection,
  collection:
    selection.mode === 'in'
      ? [...selection.collection, elementToSelect]
      : selection.collection.filter(element => element !== elementToSelect),
});
const unselect = <Type>(selection: Selection<Type>, elementToUnselect: Type): Selection<Type> => ({
  ...selection,
  collection:
    selection.mode === 'in'
      ? selection.collection.filter(element => element !== elementToUnselect)
      : [...selection.collection, elementToUnselect],
});
const currentSelectionState = <Type>(selection: Selection<Type>, totalCount: number): 'mixed' | boolean => {
  if (selection.collection.length === totalCount) {
    return selection.mode === 'in';
  } else if (selection.collection.length !== 0) {
    return 'mixed';
  } else {
    return selection.mode === 'not_in';
  }
};

const useSelection = <Type = string>(totalCount: number) => {
  const [selection, setSelection] = useState<Selection<Type>>({
    mode: 'in',
    collection: [],
  });

  const isSelected = useCallback(
    (item: Type) => {
      return selection.mode === 'in' ? selection.collection.includes(item) : !selection.collection.includes(item);
    },
    [selection]
  );

  const onSelectionChange = useCallback((item: Type, newValue: boolean) => {
    setSelection(selection => (newValue ? select<Type>(selection, item) : unselect<Type>(selection, item)));
  }, []);

  const onSelectAllChange = useCallback((newValue: boolean) => {
    setSelection(selection => (newValue ? selectAll<Type>(selection) : unselectAll<Type>(selection)));
  }, []);

  return [
    selection.collection,
    currentSelectionState(selection, totalCount),
    isSelected,
    onSelectionChange,
    onSelectAllChange,
  ] as const;
};

export {useSelection};
