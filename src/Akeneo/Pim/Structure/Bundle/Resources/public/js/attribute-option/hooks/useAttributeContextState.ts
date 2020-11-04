import {AttributeContextState} from '../contexts';
import {useCallback, useState} from 'react';
import {ATTRIBUTE_OPTIONS_AUTO_SORT} from '../model';

const useAttributeContextState = (attributeId: number, initialAutoSortOptions: boolean): AttributeContextState => {
  const [autoSortOptions, setAutoSortOptions] = useState<boolean>(initialAutoSortOptions);

  const toggleAutoSortOptions = useCallback(() => {
    const newValue = !autoSortOptions;
    setAutoSortOptions(newValue);
    window.dispatchEvent(
      new CustomEvent(ATTRIBUTE_OPTIONS_AUTO_SORT, {
        detail: {
          autoSortOptions: newValue,
        },
      })
    );
  }, [setAutoSortOptions, autoSortOptions]);

  return {
    attributeId,
    autoSortOptions,
    toggleAutoSortOptions,
  };
};

export default useAttributeContextState;
