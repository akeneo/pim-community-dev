import React, {createContext, FC, useEffect} from 'react';
import {fromPairs} from 'lodash/fp';
import {Channel, Locale, useFetch, useRoute} from '@akeneo-pim-community/shared';

type SetCanLeavePage = (canLeavePage: boolean) => void;

type CanLeavePageContextContent = {
  setCanLeavePage: SetCanLeavePage;
};

const CanLeavePageContext = createContext<CanLeavePageContextContent>({
  setCanLeavePage: () => {},
});

type Props = {
  setCanLeavePage: SetCanLeavePage;
};

const CanLeavePageProvider: FC<Props> = ({children, setCanLeavePage}) => {
  return <CanLeavePageContext.Provider value={{setCanLeavePage}}>{children}</CanLeavePageContext.Provider>;
};

export {CanLeavePageProvider, CanLeavePageContext};
