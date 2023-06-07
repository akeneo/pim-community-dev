import React, {createContext, FC, useState} from 'react';

type SaveStatusState = {
  globalStatus: string;
  handleStatusListChange: (id: string, status: string) => void;
};

const SaveStatusContext = createContext<SaveStatusState>({globalStatus: 'Saved', handleStatusListChange: () => {}});

const resolveGlobalStatus = (statusList: string[]): string => {
  // return () => {
  //   return 'saved'
  // }
  return 'Saving';
};

const SaveStatusProvider: FC = ({children}) => {
  const [statusList, setStatusList] = useState([]);
  const globalStatus = resolveGlobalStatus(statusList);

  const handleStatusListChange = (id: string, status: string) => {
    setStatusList(previousStatus => ({
      ...previousStatus,
      [id]: status,
    }));
  };

  return (
    <SaveStatusContext.Provider value={{globalStatus: globalStatus, handleStatusListChange: handleStatusListChange}}>
      {children}
    </SaveStatusContext.Provider>
  );
};

export {SaveStatusContext, SaveStatusProvider};
