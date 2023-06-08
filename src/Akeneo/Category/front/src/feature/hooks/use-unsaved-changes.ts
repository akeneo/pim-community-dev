import {useState, useCallback, useEffect, Dispatch} from 'react';

const useUnsavedChanges = (beforeUnloadMessage: string): [boolean, Dispatch<boolean>, Dispatch<string>] => {
  const [isSaved, setSaved] = useState<boolean>(true);
  const [message, setMessage] = useState<string>(beforeUnloadMessage);

  const handleUnload = useCallback(
    (event: BeforeUnloadEvent) => {
      if (isSaved) {
        return;
      }

      event.preventDefault();
      event.returnValue = message;
      return message;
    },
    [isSaved, message]
  );

  /* istanbul ignore next */
  useEffect(() => {
    window.addEventListener('beforeunload', handleUnload);

    return () => window.removeEventListener('beforeunload', handleUnload);
  }, [handleUnload]);

  return [isSaved, setSaved, setMessage];
};

export {useUnsavedChanges};
