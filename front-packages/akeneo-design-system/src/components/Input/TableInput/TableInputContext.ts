import {createContext} from 'react';

type TableInputContextType = {
  readOnly: boolean;
};

const TableInputContext = createContext<TableInputContextType>({
  readOnly: false,
});

export {TableInputContext};
