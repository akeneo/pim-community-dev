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
        retrieveInvitedUsers().then((invitedUsers) => {
            setInvitedUsers(invitedUsers);
        });
    }, [retrieveInvitedUsers]);

    const addInvitedUsers = (newInvitedUsers: string[]) => {
        saveNewInvitedUsers(newInvitedUsers).then((success) => {
            if (success) {
                retrieveInvitedUsers().then((invitedUsers) => {
                    setInvitedUsers(invitedUsers);
                });

                notify(
                    NotificationLevel.SUCCESS,
                    translate('free_trial.invite_users.invite_messages.success.title'),
                    translate('free_trial.invite_users.invite_messages.success.description')
                );
            } else {
                notify(
                    NotificationLevel.ERROR,
                    translate('free_trial.invite_users.invite_messages.error.title'),
                    translate('free_trial.invite_users.invite_messages.error.description')
                );
            }
        });
    }

    return {invitedUsers, addInvitedUsers};
}

export {useInvitedUsers};
