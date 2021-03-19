import React from "react";
import { LocaleCode } from "@akeneo-pim-community/shared";
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from "styled-components";
import {Badge, IconButton, CheckIcon, CloseIcon, Locale} from 'akeneo-design-system';

const Header = styled.div`
  height: 54px;
  display: flex;
  align-items: center;
  & > * {
    margin-right: 10px;
  }
`;

const Document = styled.a`
  color: #9452ba;
  font-size: 17px;
  font-style: italic;
  font-weight: bold;
`;

const Highlight = styled.span`
  color: #9452ba;
`;

const Change = styled.div<{isSame: boolean}>`
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
    background: #f6dfdc;
  }
  
  ins {
    text-decoration: none;
    background: #e1f0e3;
  }
`

const Attribute = styled.span`
  color: #11324d;
  font-style: italic;
  font-weight: bold;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`
const OldValue = styled.span`
  color: #d4604f;
  margin-right: 5px;
`;

const NewValue = styled.span`
  color: #67b373;
  margin-right: 5px;
`

const ActionsContainer = styled.div`
  & > *:nth-child(1) {
    margin-right: 10px;
  }
`

const LocaleScope = styled.div`
  white-space: nowrap;
  &:before {
    content: ' - ';
    margin-left: 5px;
  }
  & > :nth-child(2) {
    margin-left: 5px;
  }
`;

type ScopeCode = string;

type InProgressProposal = {
  status: 'in_progress';
  status_label: string;
};

type ProposalChange = {
  localeLabel: string;
  attributeLabel: string;
  before: string;
  after: string;
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
  changes: {
    [attributeCode: string]: ProposalChange[]
  }
};

type ProposalProps = {
  formattedChanges: InProgressProposal | ReadyProposal;
  documentType: 'product_draft';
  documentId: number;
  documentLabel: string;
  authorLabel: string;
  createdAt: string;
}

const Proposal: React.FC<ProposalProps> = ({
  formattedChanges,
  documentId,
  documentType,
  documentLabel,
  authorLabel,
  createdAt,
  children
}) => {
  const router = useRouter();
  const translate = useTranslate();

  const isSame: (change: ProposalChange) => boolean = (change) => change.before == change.after;

  return (
    <>
      <Header>
        <Document href={`#${router.generate(documentType === 'product_draft' ? 'pim_enrich_product_edit' : 'pim_enrich_product_model_edit', { id: documentId })}`}>
          {documentLabel}
        </Document>
        <Badge level="tertiary" title={ translate(`pim_datagrid.workflow.status_message.${formattedChanges.status_label}`) }>
          { translate(`pim_datagrid.workflow.status.${formattedChanges.status_label}`) }
        </Badge>
        <span>
          { translate('pim_datagrid.workflow.by') }
          <Highlight>{ authorLabel }</Highlight>
          { translate('pim_datagrid.workflow.at') }&nbsp;
          <Highlight>{ createdAt }</Highlight>
        </span>
      </Header>
      {formattedChanges.status === 'in_progress' &&
        translate('pim_datagrid.workflow.draft_in_progress', { author: authorLabel })
      }
      {formattedChanges.status === 'ready' &&
      <>
        {Object.keys(formattedChanges.changes).map((attributeCode) =>
          <div key={attributeCode}>
            {formattedChanges.status === 'ready' && formattedChanges.changes[attributeCode].map((change, i) =>
              <Change key={`${attributeCode}-${i}`} isSame={isSame(change)}>
                <div style={{display: 'flex', overflow: 'hidden'}}>
                  <Attribute title={change.attributeLabel}>{change.attributeLabel}</Attribute>
                  { change.scope && change.locale &&
                    <LocaleScope>
                      {change.scope &&
                      <span>{change.scope}</span>
                      }
                      {change.locale &&
                      <Locale
                        code={change.locale}
                        languageLabel={change.localeLabel}
                      />
                      }
                    </LocaleScope>
                  }
                </div>
                {isSame &&
                  <div>
                    <OldValue>{translate('pim_datagrid.workflow.old_value')}</OldValue>
                    <span dangerouslySetInnerHTML={{__html: change.before}}/>
                  </div>
                }
                {isSame &&
                  <div>
                    <NewValue>{translate('pim_datagrid.workflow.new_value')}</NewValue>
                    <span dangerouslySetInnerHTML={{__html: change.after}}/>
                  </div>
                }
                {!isSame &&
                  <div>
                    {translate('pim_datagrid.workflow.no_diff', { value: change.before })}
                  </div>
                }
                {change.canReview &&
                <ActionsContainer>
                  <IconButton
                    className='partial-approve-link'
                    ghost
                    icon={<CheckIcon />}
                    size="small"
                    title={translate('pim_datagrid.workflow.partial_approve', {
                      attribute: change.attributeLabel,
                      product: documentLabel,
                    })}
                    level="primary"
                    data-product={ formattedChanges.search_id }
                    data-document-type={ formattedChanges.document_type }
                    data-scope={ change.scope }
                    data-locale={ change.locale }
                    data-attribute={ attributeCode }
                    data-author={ formattedChanges.author_code }
                    data-draft={ formattedChanges.id }
                    data-action="approve"
                  />
                  <IconButton
                    className='partial-reject-link'
                    ghost
                    icon={<CloseIcon />}
                    size="small"
                    title={translate('pim_datagrid.workflow.partial_reject', {
                      attribute: change.attributeLabel,
                      product: documentLabel,
                    })}
                    level="danger"
                    data-product={ formattedChanges.search_id }
                    data-document-type={ formattedChanges.document_type }
                    data-scope={ change.scope }
                    data-locale={ change.locale }
                    data-attribute={ attributeCode }
                    data-author={ formattedChanges.author_code }
                    data-draft={ formattedChanges.id }
                    data-action="reject"
                  />
                </ActionsContainer>
              }
              </Change>
            )}
          </div>
        )}
      </>
      }
    </>
  );
};

export { Proposal }
