import {useState, useCallback, useEffect} from 'react';

const useUnsavedChanges = <ValueType>(
  beforeUnloadMessage: string
): [boolean, (newValue: ValueType) => void, (newValue: ValueType) => void] => {
  const [isModified, setModified] = useState<boolean>(false);
  const [initialValue, setInitialValue] = useState<string | null>(null);
  const updateValue = useCallback(
    (newValue: ValueType) => {
      if (null === newValue) {
        return;
      }

      if (null === initialValue) {
        setInitialValue(JSON.stringify(newValue));

        return;
      }

      setModified(initialValue !== JSON.stringify(newValue));
    },
    [setModified, setInitialValue, initialValue]
  );

  const resetValue = useCallback(
    (newValue: ValueType) => {
      setInitialValue(JSON.stringify(newValue));
      setModified(false);
    },
    [setModified, setInitialValue]
  );

  /* istanbul ignore next */
  const handleUnload = useCallback(
    (event: BeforeUnloadEvent) => {
      if (!isModified) {
        return;
      }

      event.preventDefault();
      event.returnValue = beforeUnloadMessage;

      return beforeUnloadMessage;
    },
    [isModified, beforeUnloadMessage]
  );

  /* istanbul ignore next */
  useEffect(() => {
    window.addEventListener('beforeunload', handleUnload);

    return () => window.removeEventListener('beforeunload', handleUnload);
  }, [handleUnload]);

  return [isModified, updateValue, resetValue];
};

export {useUnsavedChanges};
