import React, {useState} from 'react';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb, Button, Field, Helper, Table, TagInput, SurveyIllustration, Badge} from 'akeneo-design-system';
import styled from 'styled-components';
import {InvitedUser} from './models';
import {useInvitedUsers} from './hooks';

const FieldContent = styled.div`
  display: flex;
  align-items: center;
`;
const TagInputContainer = styled.div`
  flex: 1;
  margin-right: 10px;
`;

const DivContainer = styled.div`
  font-size: 15px;
`;

const FieldContainer = styled(Field)`
  width: 500px;
  margin: 30px auto 20px auto;
`;

const IllustrationContainer = styled.div`
  text-align: center;
  width: 500px;
  margin: 0 auto;
`;

const InviteUserApp = () => {
  const translate = useTranslate();
  const [newInvitedUsers, setNewInvitedUsers] = useState<string[]>([]);
  const {invitedUsers, addInvitedUsers} = useInvitedUsers();

  const addNewUsers = () => {
    addInvitedUsers(newInvitedUsers);

    setNewInvitedUsers([]);
  };

  const handleInvitedUsersChange = (emails: string[]) => {
    const validEmails = emails.filter((email: string) => {
      return email.match(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/);
    })
    setNewInvitedUsers(validEmails);
  }

  return (
    <>
      <PageHeader>
        <PageHeader.Title>
          {translate('free_trial.invite_users.title', {count: invitedUsers.length}, invitedUsers.length)}
        </PageHeader.Title>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step>{translate('free_trial.invite_users.breadcrumb')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
      </PageHeader>
      <PageContent>
        <Helper level="info">
          <p>{translate('free_trial.invite_users.invite_helper.message1')}</p>
          <p>{translate('free_trial.invite_users.invite_helper.message2')}</p>
        </Helper>
        <FieldContainer label={translate('free_trial.invite_users.invite_input_label')}>
          <FieldContent>
            <TagInputContainer>
              <TagInput onChange={handleInvitedUsersChange} value={newInvitedUsers} />
            </TagInputContainer>
            <Button ghost level="tertiary" disabled={newInvitedUsers.length < 1} onClick={() => addNewUsers()}>
              {translate('pim_common.add')}
            </Button>
          </FieldContent>
        </FieldContainer>
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('free_trial.invite_users.users_list.headers.invited_user')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('free_trial.invite_users.users_list.headers.status')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {invitedUsers &&
              invitedUsers.map((invitedUser: InvitedUser) => (
                <Table.Row key={invitedUser.email}>
                  <Table.Cell rowTitle>{invitedUser.email}</Table.Cell>
                  <Table.Cell>
                    <Badge level={invitedUser.status === 'active' ? 'primary' : 'tertiary'}>{invitedUser.status}</Badge>
                  </Table.Cell>
                </Table.Row>
              ))}
          </Table.Body>
        </Table>
        {invitedUsers && invitedUsers.length === 0 && (
          <IllustrationContainer>
            <SurveyIllustration />
            <DivContainer>
              <div>{translate('free_trial.invite_users.users_list.empty_list_message')}</div>
            </DivContainer>
          </IllustrationContainer>
        )}
      </PageContent>
    </>
  );
};

export {InviteUserApp};
