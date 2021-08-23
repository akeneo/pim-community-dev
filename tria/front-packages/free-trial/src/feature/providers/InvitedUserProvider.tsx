import React, {createContext, FC} from 'react';
import {InvitedUser} from '../models';

export type InviteUsersResponse = {
  success: boolean;
  errors: string[];
}

type InvitedUserState = {
  saveNewInvitedUsers: (emails: string[]) => Promise<InviteUsersResponse>;
  retrieveInvitedUsers: () => Promise<InvitedUser[]>;
};

const InvitedUserContext = createContext<InvitedUserState>({
  saveNewInvitedUsers: () => Promise.resolve({success: false, errors: []}),
  retrieveInvitedUsers: () => Promise.resolve([]),
});


const InvitedUserProvider: FC<InvitedUserState> = ({children, saveNewInvitedUsers, retrieveInvitedUsers}) => {
  return <InvitedUserContext.Provider value={{saveNewInvitedUsers: saveNewInvitedUsers, retrieveInvitedUsers}}>{children}</InvitedUserContext.Provider>;
};

export {InvitedUserProvider, InvitedUserContext};
