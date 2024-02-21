import * as React from 'react';

export const usePreventClosing = (isDirty: () => boolean, message: string) => {
  const handleUnload = React.useCallback(
    (event: BeforeUnloadEvent) => {
      if (!isDirty()) {
        return;
      }

      event.preventDefault();
      event.returnValue = message;

      return message;
    },
    [isDirty, message]
  );

  React.useEffect(() => {
    window.addEventListener('beforeunload', handleUnload);

    return () => window.removeEventListener('beforeunload', handleUnload);
  }, [handleUnload]);
};
