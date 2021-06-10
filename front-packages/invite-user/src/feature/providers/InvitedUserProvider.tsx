import React, {createContext, FC} from 'react';
import {InvitedUser} from '../models';

type InvitedUserState = {
  inviteNewUsers: (emails: string[]) => void;
  retrieveInvitedUsers: () => InvitedUser[];
};

const InvitedUserContext = createContext<InvitedUserState>({
  inviteNewUsers: () => {},
  retrieveInvitedUsers: () => [],
});

type Props = {
  inviteNewUsers: (emails: string[]) => void;
  retrieveInvitedUsers: () => InvitedUser[];
};

const InvitedUserProvider: FC<Props> = ({children, inviteNewUsers, retrieveInvitedUsers}) => {
  return <InvitedUserContext.Provider value={{inviteNewUsers, retrieveInvitedUsers}}>{children}</InvitedUserContext.Provider>;
};

export {InvitedUserProvider, InvitedUserContext};
