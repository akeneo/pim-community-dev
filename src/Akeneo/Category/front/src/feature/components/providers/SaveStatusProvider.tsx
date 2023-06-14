import React, {createContext, useState} from 'react';

// Status values represent each status priority. The higher the value, the higher the priority.
// The highest value is the displayed status.
enum Status {
  SAVED = 0,
  ERRORS = 1,
  SAVING = 2,
  EDITING = 3,
}

type SaveStatusState = {
  globalStatus: number;
  handleStatusListChange: (id: string, status: Status) => void;
};

const SaveStatusContext = createContext<SaveStatusState | null>(null);

const resolveGlobalStatus = (statusList: {[id: string]: number}): number => {
  let globalStatus = Status.SAVED;
  if (Object.keys(statusList).length > 0) {
    globalStatus = Object.values(statusList).reduce((previousGlobalStatus: number, currentFieldStatus: number) => {
      return Math.max(Number(previousGlobalStatus), Number(currentFieldStatus));
    });
  }

  return globalStatus;
};

type Props = {
  children: React.ReactNode;
  onSaveStatusChange?: (status: Status) => void;
};

const SaveStatusProvider = ({children, onSaveStatusChange}: Props) => {
  const [, setStatusList] = useState<{[id: string]: number}>({});
  const [globalStatus, setGlobalStatus] = useState(Status.SAVED);

  const handleStatusListChange = (id: string, status: Status) => {
    setStatusList(previousStatusList => {
      const statusList = {
        ...previousStatusList,
        [id]: status,
      };

      setGlobalStatus(previousGlobalStatus => {
        const globalStatus = resolveGlobalStatus(statusList);
        if (previousGlobalStatus !== globalStatus) {
          console.debug('Save status:', Status[globalStatus], statusList);
          if (onSaveStatusChange) {
            onSaveStatusChange(globalStatus);
          }
        }
        return globalStatus;
      });

      return statusList;
    });
  };

  return (
    <SaveStatusContext.Provider value={{globalStatus, handleStatusListChange}}>{children}</SaveStatusContext.Provider>
  );
};

export {SaveStatusContext, SaveStatusProvider, Status};
