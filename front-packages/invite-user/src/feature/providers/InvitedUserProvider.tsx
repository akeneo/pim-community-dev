import React, {createContext, FC} from 'react';
import {InvitedUser} from '../models';

type InvitedUserState = {
  newInvitedUsers: (emails: string[]) => void;
  retrieveInvitedUsers: () => InvitedUser[];
};

const InvitedUserContext = createContext<InvitedUserState>({
  newInvitedUsers: () => {},
  retrieveInvitedUsers: () => [],
});

type Props = {
  newInvitedUsers: (emails: string[]) => void;
  retrieveInvitedUsers: () => InvitedUser[];
};

const InvitedUserProvider: FC<Props> = ({children, newInvitedUsers, retrieveInvitedUsers}) => {
  return <InvitedUserContext.Provider value={{newInvitedUsers, retrieveInvitedUsers}}>{children}</InvitedUserContext.Provider>;
};

export {InvitedUserProvider, InvitedUserContext};
