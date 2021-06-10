import {InvitedUser} from '../models';
import {useState} from 'react';

const useInvitedUsers = () => {
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
  }

  return {invitedUsers, addInvitedUsers};
}

export {useInvitedUsers};
