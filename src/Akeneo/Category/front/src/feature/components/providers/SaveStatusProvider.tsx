import React, {createContext, FC, useState} from 'react';

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

const SaveStatusContext = createContext<SaveStatusState>({
  globalStatus: Status.SAVED,
  handleStatusListChange: () => {},
});

const resolveGlobalStatus = (statusList: {[id: string]: number}): number => {
  let globalStatus = Status.SAVED;
  if (Object.keys(statusList).length > 0) {
    globalStatus = Object.values(statusList).reduce((previousGlobalStatus: number, currentFieldStatus: number) => {
      return Math.max(Number(previousGlobalStatus), Number(currentFieldStatus));
    });
  }

  return globalStatus;
};

const SaveStatusProvider: FC = ({children}) => {
  const [statusList, setStatusList] = useState<{[id: string]: number}>({});
  const globalStatus = resolveGlobalStatus(statusList);

  const handleStatusListChange = (id: string, status: Status) => {
    if (Object.values(Status).includes(status as Status)) {
      setStatusList(previousStatus => ({
        ...previousStatus,
        [id]: status,
      }));
    }
  };

  return (
    <SaveStatusContext.Provider value={{globalStatus: globalStatus, handleStatusListChange: handleStatusListChange}}>
      {children}
    </SaveStatusContext.Provider>
  );
};

export {SaveStatusContext, SaveStatusProvider, Status};
