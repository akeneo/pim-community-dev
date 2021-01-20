import {useState, useCallback, useEffect, EffectCallback} from 'react';

const useUnsavedChanges = <ValueType>(entity: ValueType, beforeUnloadMessage: string): [boolean, EffectCallback] => {
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

  const resetValue = useCallback(() => {
    setInitialValue(JSON.stringify(entity));
    setModified(false);
  }, [setModified, setInitialValue, entity]);

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

  useEffect(() => updateValue(entity), [entity, updateValue]);

  return [isModified, resetValue];
};

export {useUnsavedChanges};
