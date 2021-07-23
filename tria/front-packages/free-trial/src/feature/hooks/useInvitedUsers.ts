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
        saveNewInvitedUsers(newInvitedUsers)
            .then((response) => {
                if (response.success) {
                    notify(
                        NotificationLevel.SUCCESS,
                        translate('free_trial.invite_users.invite_messages.success.title'),
                        translate('free_trial.invite_users.invite_messages.success.description')
                    );
                }
                response.errors.forEach((error: string) => {
                    notify(
                      NotificationLevel.ERROR,
                      translate(`free_trial.invite_users.invite_messages.error.${error}.title`),
                      translate(`free_trial.invite_users.invite_messages.error.${error}.description`)
                    );
                });
                retrieveInvitedUsers().then((invitedUsers) => {
                    setInvitedUsers(invitedUsers);
                });
            }, () => {
                notify(
                    NotificationLevel.ERROR,
                    translate(`free_trial.invite_users.invite_messages.error.invitation_failed.title`),
                    translate(`free_trial.invite_users.invite_messages.error.invitation_failed.description`)
                );
            });
    }

    return {invitedUsers, addInvitedUsers};
}

export {useInvitedUsers};
