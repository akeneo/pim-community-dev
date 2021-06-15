import React, {createContext, FC} from 'react';
import {InvitedUser} from '../models';

type InvitedUserState = {
  saveNewInvitedUsers: (emails: string[]) => Promise<boolean>;
  retrieveInvitedUsers: () => Promise<InvitedUser[]>;
};

const InvitedUserContext = createContext<InvitedUserState>({
  saveNewInvitedUsers: () => Promise.resolve(true),
  retrieveInvitedUsers: () => Promise.resolve([]),
});


const InvitedUserProvider: FC<InvitedUserState> = ({children, saveNewInvitedUsers, retrieveInvitedUsers}) => {
  return <InvitedUserContext.Provider value={{saveNewInvitedUsers: saveNewInvitedUsers, retrieveInvitedUsers}}>{children}</InvitedUserContext.Provider>;
};

export {InvitedUserProvider, InvitedUserContext};
