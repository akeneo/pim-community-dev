import React from 'react';
import {useGetProposalsToReview} from '../../hooks';
import {Proposal} from '../../domain';
import {Button, SectionTitle, SettingsIllustration, Table, Link} from 'akeneo-design-system';
import {NoDataSection, NoDataText, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const WorkflowWidget = () => {
  const translate = useTranslate();
  const router = useRouter();

  const proposalsToReview: Proposal[] | null = useGetProposalsToReview();

  return null === proposalsToReview ? null : (
    <Container>
      <SectionTitle>
        <SectionTitle.Title>{translate('pimee_dashboard.widget.product_drafts.title')}</SectionTitle.Title>
        {proposalsToReview.length > 0 && (
          <>
            <SectionTitle.Spacer />
            <Button
              ghost
              size={'small'}
              level={'tertiary'}
              title={translate('pimee_dashboard.widget.product_drafts.view_all')}
              onClick={() => router.redirect(router.generate('pimee_workflow_proposal_index'))}
            >
              {translate('pimee_dashboard.widget.product_drafts.view_all')}
            </Button>
          </>
        )}
      </SectionTitle>

      {proposalsToReview.length === 0 && (
        <NoDataSection style={{marginTop: 0}}>
          <SettingsIllustration size={128} />
          <NoDataText style={{fontSize: '15px'}}>{translate('pimee_dashboard.widget.product_drafts.empty')}</NoDataText>
        </NoDataSection>
      )}

      {proposalsToReview.length > 0 && (
        // class name added only for end-to-end tests
        <Table className="dashboard-widget-proposals-to-review">
          <Table.Header>
            <Table.HeaderCell>{translate('pimee_dashboard.widget.product_drafts.date')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pimee_dashboard.widget.product_drafts.author')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pimee_dashboard.widget.product_drafts.product')}</Table.HeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {proposalsToReview.map((proposal: Proposal, index: number) => {
              return (
                <Table.Row key={`proposal${index}`}>
                  <Table.Cell>{proposal.createdAt}</Table.Cell>
                  <Table.Cell>{proposal.authorFullName}</Table.Cell>
                  <Table.Cell>
                    <Link href={`#${proposal.productEditUrl}`} target="_self">
                      {proposal.productLabel}
                    </Link>
                  </Table.Cell>
                  <TableActionCell>
                    <Button
                      ghost
                      size={'small'}
                      level={'tertiary'}
                      title={translate('pimee_dashboard.widget.product_drafts.review')}
                      onClick={() => router.redirect(proposal.productReviewUrl)}
                    >
                      {translate('pimee_dashboard.widget.product_drafts.review')}
                    </Button>
                  </TableActionCell>
                </Table.Row>
              );
            })}
          </Table.Body>
        </Table>
      )}
    </Container>
  );
};

const Container = styled.div`
  margin-top: 30px;
`;

const TableActionCell = styled(Table.Cell)`
  width: 50px;
`;

export {WorkflowWidget};
