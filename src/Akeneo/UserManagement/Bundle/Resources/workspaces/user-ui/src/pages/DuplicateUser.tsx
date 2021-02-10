import React from 'react';
import {Modal, SectionTitle, Title, UsersIllustration} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {CreateUserForm} from '../components';

type DuplicateUserProps = {
  userId: number;
  userCode: string;
  onCancel: () => void;
  onDuplicateSuccess: (userId: string) => void;
};

const DuplicateUser = ({userId, userCode, onCancel, onDuplicateSuccess}: DuplicateUserProps) => {
  const translate = useTranslate();
  const notify = useNotify();

  const onSuccess = (newUserId: string): void => {
    notify(NotificationLevel.SUCCESS, translate('pim_user_management.form.duplication.notification.success'));
    onDuplicateSuccess(newUserId);
  };
  const onError = (): void => {
    notify(NotificationLevel.ERROR, translate('pim_user_management.form.duplication.notification.failure'));
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<UsersIllustration />}>
      <SectionTitle color={'brand'} size={'bigger'}>
        {translate('pim_menu.item.user')}
      </SectionTitle>
      <Title>{translate('pim_user_management.form.duplication.title', {username: userCode})}</Title>
      <CreateUserForm userId={userId} onCancel={onCancel} onSuccess={onSuccess} onError={onError} />
    </Modal>
  );
};

export default DuplicateUser;
