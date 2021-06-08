import React from 'react';
import {PageContent, PageHeader, useTranslate} from "@akeneo-pim-community/shared";
import {Breadcrumb, Button, Field, Helper, Table, TagInput, SurveyIllustration, Badge} from "akeneo-design-system";
import styled from "styled-components";

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

type User = {
    email: string;
    status: 'invited' | 'active';
}

const InviteUserApp = () => {
    const translate = useTranslate();

    const users: User[] = [
        {email: 'test@test.com', status: "invited"},
        {email: 'test1@test1.com', status: "active"},
        {email: 'test2@test2.com', status: "invited"},
        {email: 'test3@test3.com', status: "active"},
    ];

    return (
        <>
            <PageHeader>
                <PageHeader.Title>
                    {translate('free_trial.invite_users.title', {count: 0}, 0)}
                </PageHeader.Title>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step>
                            {translate('free_trial.invite_users.breadcrumb')}
                        </Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.Actions>
                    <Button
                        ghost
                        level="secondary"
                        onClick={function noRefCheck() {
                        }}
                    >
                        {translate('free_trial.invite_users.invite_button')}
                    </Button>
                </PageHeader.Actions>
            </PageHeader>
            <PageContent>
                <Helper level="info">
                    <p>{translate('free_trial.invite_users.invite_helper.message1')}</p>
                    <p>{translate('free_trial.invite_users.invite_helper.message2')}</p>
                </Helper>
                <FieldContainer label={translate('free_trial.invite_users.invite_input_label')}>
                    <FieldContent>
                        <TagInputContainer>
                            <TagInput
                                onChange={() => {
                                }}
                                value={[]}
                            />
                        </TagInputContainer>
                        <Button ghost level="tertiary" onClick={() => {
                        }}>
                            {translate('pim_common.add')}
                        </Button>
                    </FieldContent>
                </FieldContainer>
                <Table>
                    <Table.Header>
                        <Table.HeaderCell>
                            {translate('free_trial.invite_users.users_list.headers.invited_user')}
                        </Table.HeaderCell>
                        <Table.HeaderCell>
                            {translate('free_trial.invite_users.users_list.headers.status')}
                        </Table.HeaderCell>
                    </Table.Header>
                    <Table.Body>
                        {users.map(user =>
                            <Table.Row>
                                <Table.Cell rowTitle>
                                    {user.email}
                                </Table.Cell>
                                <Table.Cell>
                                    <Badge level={user.status === 'active' ? 'primary' : 'tertiary'}>
                                        {user.status}
                                    </Badge>
                                </Table.Cell>
                            </Table.Row>
                        )}
                    </Table.Body>
                </Table>
                {users.length === 0 &&
                    <IllustrationContainer>
                        <SurveyIllustration/>
                        <div>
                            {translate('free_trial.invite_users.users_list.empty_list_message')}
                        </div>
                    </IllustrationContainer>
                }
            </PageContent>
        </>
    );
}

export {InviteUserApp};
