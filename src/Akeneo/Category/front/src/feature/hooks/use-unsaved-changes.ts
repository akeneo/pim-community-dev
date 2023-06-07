import {useState, useCallback, useEffect, Dispatch} from 'react';

const useUnsavedChanges = (beforeUnloadMessage: string): [boolean, Dispatch<boolean>] => {
  const [isSaved, setSaved] = useState<boolean>(true);

  const handleUnload = useCallback(
    (event: BeforeUnloadEvent) => {
      if (isSaved) {
        return;
      }

      event.preventDefault();
      event.returnValue = beforeUnloadMessage;

      return beforeUnloadMessage;
    },
    [isSaved, beforeUnloadMessage]
  );

  /* istanbul ignore next */
  useEffect(() => {
    window.addEventListener('beforeunload', handleUnload);

    return () => window.removeEventListener('beforeunload', handleUnload);
  }, [handleUnload]);

  return [isSaved, setSaved];
};

export {useUnsavedChanges};
