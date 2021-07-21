import React, {FC} from 'react';
import {InvitedUser, InvitedUserProvider, InviteUsersResponse} from 'akeneo-pim-free-trial';
import {useRoute} from "@akeneo-pim-community/shared";

const PimInvitedUserProvider: FC = ({children}) => {
  const retrieveUsersUrl = useRoute('akeneo_free_trial_retrieve_users');
  const saveUsersUrl = useRoute('akeneo_free_trial_save_users');

  const retrieveInvitedUsers = async(): Promise<InvitedUser[]> => {
    const response = await fetch(retrieveUsersUrl);

    return await response.json();
  }

  const saveInvitedUsers = async(emails: string[]): Promise<InviteUsersResponse> => {
    const response = await fetch(saveUsersUrl, {method: 'POST', body: JSON.stringify(emails)});

    return response.json();
  }

  return (
    <InvitedUserProvider saveNewInvitedUsers={saveInvitedUsers} retrieveInvitedUsers={retrieveInvitedUsers}>
      {children}
    </InvitedUserProvider>
  );
};

export {PimInvitedUserProvider};
