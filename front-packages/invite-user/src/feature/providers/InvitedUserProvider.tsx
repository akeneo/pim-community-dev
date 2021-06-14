import React, {createContext, FC} from 'react';
import {InvitedUser} from '../models';

type InvitedUserState = {
  saveNewInvitedUsers: (emails: string[]) => InvitedUser[];
  retrieveInvitedUsers: () => InvitedUser[];
};

const InvitedUserContext = createContext<InvitedUserState>({
  saveNewInvitedUsers: () => [],
  retrieveInvitedUsers: () => [],
});

const InvitedUserProvider: FC<InvitedUserState> = ({children, saveNewInvitedUsers, retrieveInvitedUsers}) => {
  return <InvitedUserContext.Provider value={{saveNewInvitedUsers: saveNewInvitedUsers, retrieveInvitedUsers}}>{children}</InvitedUserContext.Provider>;
};

export {InvitedUserProvider, InvitedUserContext};
