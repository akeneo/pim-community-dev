import React from 'react';
import {LocaleCode} from '@akeneo-pim-community/shared';
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {AkeneoThemedProps, Badge, getColor, getFontSize} from 'akeneo-design-system';
import {ApproveAllButton, ApproveButton, RejectAllButton, RejectButton, RemoveAllButton} from './proposalActions';
import {ScopeLabel} from './ScopeLabel';
import {LocaleLabel} from './LocaleLabel';

const ProposalContainer = styled.div`
  .proposalActionButton {
    transition: opacity 0.2s ease-in;
    opacity: 0;
  }
  &:hover .proposalActionButton {
    opacity: 1;
  }
`;

const Header = styled.div`
  display: flex;
  justify-content: space-between;
`;

const ProposalDescription = styled.div`
  height: 54px;
  display: flex;
  align-items: center;
  & > *:not(:last-child) {
    margin-right: 10px;
  }
`;

const DocumentLink = styled.a`
  color: ${getColor('brand', 100)};
  font-size: ${getFontSize('bigger')};
  font-style: italic;
  font-weight: bold;
`;

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
`;

const Change = styled.div<{isSame: boolean} & AkeneoThemedProps>`
  display: grid;
  grid-template-columns: ${({isSame}) => (isSame ? 'minmax(0, 0.7fr) 2fr 60px' : 'minmax(0, 0.7fr) 1fr 1fr 60px')};
  grid-template-rows: 1fr;
  gap: 10px 18px;
  min-height: 54px;

  & > * {
    padding: 19px 0;
  }

  del {
    text-decoration: none;
    background: ${getColor('red', 20)};
  }

  ins {
    text-decoration: none;
    background: ${getColor('green', 20)};
  }
`;

const Attribute = styled.span`
  color: ${getColor('grey', 140)};
  font-style: italic;
  font-weight: bold;
  padding-right: 5px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const OldValue = styled.span`
  color: ${getColor('red', 100)};
  margin-right: 5px;
`;

const NewValue = styled.span`
  color: ${getColor('green', 100)};
  margin-right: 5px;
`;

const ActionsContainer = styled.div`
  & > *:nth-child(1) {
    margin-right: 10px;
  }
`;

const LocaleScope = styled.div`
  white-space: nowrap;
  display: flex;
  &:before {
    content: ' - ';
    margin-right: 5px;
  }
  & > :nth-child(2) {
    margin-left: 5px;
  }
`;

type ScopeCode = string;
type AttributeCode = string;

type InProgressProposal = {
  status: 'in_progress';
  status_label: string;
  remove: boolean;
};

type ProposalChange = {
  attributeLabel: string;
  before: string;
  after: string;
  data: any;
  canReview: boolean;
  locale: LocaleCode | null;
  scope: ScopeCode | null;
};

type ReadyProposal = {
  status: 'ready';
  status_label: 'ready' | 'can_be_partially_reviewed' | 'can_not_be_approved' | 'in_progress' | 'can_not_be_deleted';
  search_id: number;
  document_type: 'product_draft';
  author_code: string;
  id: number;
  approve: boolean;
  refuse: boolean;
  changes: {
    [attributeCode: string]: ProposalChange[];
  };
};

type ProposalProps = {
  formattedChanges: InProgressProposal | ReadyProposal;
  documentType: 'product_draft' | 'product_model_draft';
  documentId: number;
  documentLabel: string;
  authorLabel: string;
  authorCode: string;
  createdAt: string;
  proposalId: number;
};

const Proposal: React.FC<ProposalProps> = ({
  formattedChanges,
  documentId,
  documentType,
  documentLabel,
  authorLabel,
  authorCode,
  createdAt,
  proposalId,
}) => {
  const router = useRouter();
  const translate = useTranslate();

  const isSame: (change: ProposalChange) => boolean = change => change.before === change.after;
  const documentUrl = `#${router.generate(
    documentType === 'product_draft' ? 'pim_enrich_product_edit' : 'pim_enrich_product_model_edit',
    {id: documentId}
  )}`;

  const flatChanges: (ProposalChange & {attributeCode: AttributeCode})[] = [];
  if (formattedChanges.status === 'ready') {
    Object.keys(formattedChanges.changes).forEach(attributeCode => {
      formattedChanges.changes[attributeCode].forEach(change => {
        flatChanges.push({...change, attributeCode: attributeCode});
      });
    });
  }

  return (
    <ProposalContainer>
      <Header>
        <ProposalDescription>
          <DocumentLink href={documentUrl}>{documentLabel}</DocumentLink>
          <Badge
            level="tertiary"
            title={translate(`pim_datagrid.workflow.status_message.${formattedChanges.status_label}`)}
          >
            {translate(`pim_datagrid.workflow.status.${formattedChanges.status_label}`)}
          </Badge>
          <span>
            {translate('pim_datagrid.workflow.by')}
            <Highlight>{authorLabel}</Highlight>
            {translate('pim_datagrid.workflow.at')}&nbsp;
            <Highlight>{createdAt}</Highlight>
          </span>
        </ProposalDescription>
        <ProposalDescription>
          {formattedChanges.status === 'ready' && formattedChanges.approve && (
            <ApproveAllButton productDraftType={documentType} id={proposalId} />
          )}
          {formattedChanges.status === 'ready' && formattedChanges.refuse && (
            <RejectAllButton productDraftType={documentType} id={proposalId} />
          )}
          {formattedChanges.status === 'in_progress' && formattedChanges.remove && (
            <RemoveAllButton productDraftType={documentType} id={proposalId} />
          )}
        </ProposalDescription>
      </Header>
      {formattedChanges.status === 'in_progress' &&
        translate('pim_datagrid.workflow.draft_in_progress', {author: authorLabel})}
      {formattedChanges.status === 'ready' && (
        <>
          {flatChanges.map(change => (
            <Change
              key={`${change.attributeCode}-${change.scope}-${change.locale}`}
              isSame={isSame(change)}
              data-product={documentLabel}
              data-author={authorCode}
              data-attribute={change.attributeCode}
              data-scope={change.scope}
              data-locale={change.locale}
            >
              <div style={{display: 'flex', overflow: 'hidden'}}>
                <Attribute title={change.attributeLabel}>{change.attributeLabel}</Attribute>
                {(change.scope || change.locale) && (
                  <LocaleScope>
                    {change.scope && (
                      <span>
                        <ScopeLabel scopeCode={change.scope} />
                      </span>
                    )}
                    {change.locale && <LocaleLabel localeCode={change.locale} />}
                  </LocaleScope>
                )}
              </div>
              {!isSame(change) && (
                <div>
                  <OldValue>{translate('pim_datagrid.workflow.old_value')}</OldValue>
                  <span dangerouslySetInnerHTML={{__html: change.before}} className="original-value" />
                </div>
              )}
              {!isSame(change) && (
                <div>
                  <NewValue>{translate('pim_datagrid.workflow.new_value')}</NewValue>
                  <span dangerouslySetInnerHTML={{__html: change.after}} className="new-value" />
                </div>
              )}
              {isSame(change) && (
                <div
                  dangerouslySetInnerHTML={{__html: translate('pim_datagrid.workflow.no_diff', {value: change.data})}}
                />
              )}
              {change.canReview && (
                <ActionsContainer>
                  <ApproveButton
                    id={proposalId}
                    productDraftType={documentType}
                    attributeCode={change.attributeCode}
                    attributeLabel={change.attributeLabel}
                    documentLabel={documentLabel}
                    locale={change.locale}
                    scope={change.scope}
                  />
                  <RejectButton
                    id={proposalId}
                    productDraftType={documentType}
                    attributeCode={change.attributeCode}
                    attributeLabel={change.attributeLabel}
                    documentLabel={documentLabel}
                    locale={change.locale}
                    scope={change.scope}
                  />
                </ActionsContainer>
              )}
            </Change>
          ))}
        </>
      )}
    </ProposalContainer>
  );
};

export {Proposal};
