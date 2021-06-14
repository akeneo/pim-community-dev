import {InvitedUser} from '../models';
import {useContext, useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useTranslate} from "@akeneo-pim-community/shared";
import {InvitedUserContext} from "../providers/InvitedUserProvider";

const useInvitedUsers = () => {
    const notify = useNotify();
    const translate = useTranslate();
    const {retrieveInvitedUsers, saveNewInvitedUsers} = useContext(InvitedUserContext);

    const [invitedUsers, setInvitedUsers] = useState<InvitedUser[]>([]);

    useEffect(() => {
        const users = retrieveInvitedUsers();

        setInvitedUsers(users);
    }, [retrieveInvitedUsers]);

    const addInvitedUsers = (newInvitedUsers: string[]) => {
        const newUsers = saveNewInvitedUsers(newInvitedUsers);

        setInvitedUsers(newUsers);

        notify(
            NotificationLevel.SUCCESS,
            translate('free_trial.invite_users.invite_messages.success.title'),
            translate('free_trial.invite_users.invite_messages.success.description')
        );
    }

    return {invitedUsers, addInvitedUsers};
}

export {useInvitedUsers};
