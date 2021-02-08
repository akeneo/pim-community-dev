import React from 'react';
import {Button, Modal, UsersIllustration, Field, TextInput} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import styled from 'styled-components';

const FormContainer = styled.div`
  & > * {
    margin: 0 10px 20px 0;
  }
`;

interface IndexProps {
  userId: number;
  onCancel: () => void;
}

const DuplicateUserApp = ({userId, onCancel}: IndexProps) => {
  const translate = useTranslate();

  console.log(onCancel, userId);


  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<UsersIllustration />}>

      <div className="AknFullPage-titleContainer">
        <div className="AknFullPage-subTitle">{translate('pim_menu.item.user')}</div>
        <div className="AknFullPage-title">{translate('pim_common.duplicate')}</div>
      </div>

      <FormContainer>
        <Field label={translate('pim_user_management.entity.user.properties.username')}>
          <TextInput placeholder={translate('pim_user_management.entity.user.properties.username')} value={''} onChange={() => {}} />
        </Field>
        <Field label={translate('pim_user_management.entity.user.properties.password')}>
          <TextInput placeholder={translate('pim_user_management.entity.user.properties.password')} value={''} onChange={() => {}} />
        </Field>
        <Field label={translate('pim_user_management.entity.user.properties.password_repeat')}>
          <TextInput placeholder={translate('pim_user_management.entity.user.properties.password_repeat')} value={''} onChange={() => {}} />
        </Field>
        <Field label={translate('pim_user_management.entity.user.properties.first_name')}>
          <TextInput placeholder={translate('pim_user_management.entity.user.properties.first_name')} value={''} onChange={() => {}} />
        </Field>
        <Field label={translate('pim_user_management.entity.user.properties.last_name')}>
          <TextInput placeholder={translate('pim_user_management.entity.user.properties.last_name')} value={''} onChange={() => {}} />
        </Field>
        <Field label={translate('pim_user_management.entity.user.properties.email')}>
          <TextInput placeholder={translate('pim_user_management.entity.user.properties.email')} value={''} onChange={() => {}} />
        </Field>
      </FormContainer>

      <Modal.BottomButtons>
        <Button onClick={onCancel} level={'tertiary'}>{translate('pim_common.cancel')}</Button>
        <Button>{translate('pim_common.confirm')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export default DuplicateUserApp;
