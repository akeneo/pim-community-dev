import {InvitedUser} from '../models';
import {useState} from 'react';
import {NotificationLevel, useNotify, useTranslate} from "@akeneo-pim-community/shared";

const useInvitedUsers = () => {
  const notify = useNotify();
  const translate = useTranslate();

  const [invitedUsers, setInvitedUsers] = useState<InvitedUser[]>([
    {email: 'test1@test1.com', status: 'invited'},
    {email: 'test2@test2.com', status: 'active'},
  ]);

  const addInvitedUsers = (newInvitedUsers: string[]) => {
    const tempInvitedUsers: InvitedUser[] = [];

    newInvitedUsers.forEach((newInvitedUser: string) => {
      tempInvitedUsers.push({email: newInvitedUser, status: 'invited'});
    });

    setInvitedUsers([...invitedUsers, ...tempInvitedUsers]);

    notify(
        NotificationLevel.SUCCESS,
        translate('free_trial.invite_users.invite_messages.success.title'),
        translate('free_trial.invite_users.invite_messages.success.description')
    );
  }

  return {invitedUsers, addInvitedUsers};
}

export {useInvitedUsers};
