import React, {FC} from 'react';
import {InvitedUser, InvitedUserProvider} from '@akeneo-pim-community/invite-user';
import {useRoute} from "@akeneo-pim-community/shared";

const PimInvitedUserProvider: FC = ({children}) => {
  const retrieveUsersUrl = useRoute('akeneo_free_trial_retrieve_users');
  const retrieveInvitedUsers = async(): Promise<InvitedUser[]> => {
    const response = await fetch(retrieveUsersUrl);

    return await response.json();
  }

  const saveUsers = (emails: string[]): InvitedUser[] => {
    return emails.map((email: string) => {
      return {email, status: 'invited'};
    });
  };

  return (
    <InvitedUserProvider saveNewInvitedUsers={saveUsers} retrieveInvitedUsers={retrieveInvitedUsers}>
      {children}
    </InvitedUserProvider>
  );
};

export {PimInvitedUserProvider};
