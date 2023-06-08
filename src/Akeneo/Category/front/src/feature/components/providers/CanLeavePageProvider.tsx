import React, {createContext, FC} from 'react';

type SetCanLeavePage = (canLeavePage: boolean) => void;
type SetLeavePageMessage = (message: string) => void;

type CanLeavePageContextContent = {
  setCanLeavePage: SetCanLeavePage;
  setLeavePageMessage: SetLeavePageMessage;
};

const CanLeavePageContext = createContext<CanLeavePageContextContent>({
  setCanLeavePage: () => {},
  setLeavePageMessage: () => {},
});

type Props = {
  setCanLeavePage: SetCanLeavePage;
  setLeavePageMessage: SetLeavePageMessage;
};

const CanLeavePageProvider: FC<Props> = ({children, setCanLeavePage, setLeavePageMessage}) => {
  return <CanLeavePageContext.Provider value={{setCanLeavePage, setLeavePageMessage}}>{children}</CanLeavePageContext.Provider>;
};

export {CanLeavePageProvider, CanLeavePageContext};
