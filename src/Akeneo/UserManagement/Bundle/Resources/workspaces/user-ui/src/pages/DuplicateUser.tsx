import React from 'react';
import {Modal, UsersIllustration} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {CreateUserForm} from '../components';
import {UserCode, UserId} from '../models';

type DuplicateUserProps = {
  userId: UserId;
  userCode: UserCode;
  onCancel: () => void;
  onDuplicateSuccess: (userId: UserId) => void;
};

const DuplicateUser = ({userId, userCode, onCancel, onDuplicateSuccess}: DuplicateUserProps) => {
  const translate = useTranslate();
  const notify = useNotify();

  const onSuccess = (newUserId: UserId): void => {
    notify(NotificationLevel.SUCCESS, translate('pim_user_management.form.duplication.notification.success'));
    onDuplicateSuccess(newUserId);
  };
  const onError = (): void => {
    notify(NotificationLevel.ERROR, translate('pim_user_management.form.duplication.notification.failure'));
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<UsersIllustration />}>
      <Modal.SectionTitle color={'brand'} size={'bigger'}>
        {translate('pim_menu.item.user')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_user_management.form.duplication.title', {username: userCode})}</Modal.Title>
      <CreateUserForm userId={userId} onCancel={onCancel} onSuccess={onSuccess} onError={onError} />
    </Modal>
  );
};

export default DuplicateUser;
