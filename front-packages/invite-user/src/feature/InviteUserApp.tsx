import React, {useContext, useState} from 'react';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb, Button, Field, Helper, Table, TagInput, SurveyIllustration, Badge} from 'akeneo-design-system';
import styled from 'styled-components';
import {InvitedUser} from "./models";
import {InvitedUserContext} from "./providers/InvitedUserProvider";

const FieldContent = styled.div`
  display: flex;
  align-items: center;
`;
const TagInputContainer = styled.div`
  flex: 1;
  margin-right: 10px;
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
  const [newUsers, setNewUsers] = useState<string[]>();
  const {users, addUser} = useInvitedUsers();

  return (
    <>
      <PageHeader>
        <PageHeader.Title>{translate('free_trial.invite_users.title', {count: 0}, 0)}</PageHeader.Title>
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
              <TagInput onChange={setInvitedUser} value={[]} />
            </TagInputContainer>
            <Button ghost level="tertiary" onClick={() => addInvitedUsers()}>
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
            {users.map(user => (
              <Table.Row key={user.email}>
                <Table.Cell rowTitle>{user.email}</Table.Cell>
                <Table.Cell>
                  <Badge level={user.status === 'active' ? 'primary' : 'tertiary'}>{user.status}</Badge>
                </Table.Cell>
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
        {users.length === 0 && (
          <IllustrationContainer>
            <SurveyIllustration />
            <div>{translate('free_trial.invite_users.users_list.empty_list_message')}</div>
          </IllustrationContainer>
        )}
      </PageContent>
    </>
  );
};

export {InviteUserApp};
