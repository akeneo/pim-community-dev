import {useMediator, useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {ProposalModal} from './ProposalModal';
import React from 'react';
import {Button, IconButton, useBooleanState, CheckIcon, CloseIcon} from 'akeneo-design-system';
import {LocaleCode} from '@akeneo-pim-community/shared';

type ProposalId = number;
type AttributeCode = string;
type AttributeLabel = string;
type ProductOrProductModelLabel = string;
type ScopeCode = string;

type AllProps = {
  productDraftType: 'product_draft' | 'product_model_draft';
  id: ProposalId;
};

type PartialProps = AllProps & {
  attributeCode: AttributeCode;
  attributeLabel: AttributeLabel;
  documentLabel: ProductOrProductModelLabel;
  locale: LocaleCode | null;
  scope: ScopeCode | null;
};

const ApproveAllButton: (props: AllProps) => JSX.Element = ({productDraftType, id}) => {
  const [isOpen, open, close] = useBooleanState();
  const translate = useTranslate();
  const router = useRouter();
  const getUrl = (comment: string) => router.generate(`pimee_workflow_${productDraftType}_rest_approve`, {id, comment});
  const mediator = useMediator();

  const handleClose = (successReponse?: any) => {
    if (successReponse) {
      mediator.trigger('pim_enrich:form:proposal:post_approve:success', successReponse);
    }
    close();
  };

  return (
    <>
      <Button level="primary" onClick={open} size="default">
        {translate('pim_datagrid.workflow.actions.approve_all')}
      </Button>
      {isOpen && <ProposalModal action="approve" onClose={handleClose} getUrl={getUrl} />}
    </>
  );
};

const RejectAllButton: (props: AllProps) => JSX.Element = ({productDraftType, id}) => {
  const [isOpen, open, close] = useBooleanState();
  const translate = useTranslate();
  const router = useRouter();
  const getUrl = (comment: string) => router.generate(`pimee_workflow_${productDraftType}_rest_refuse`, {id, comment});

  return (
    <>
      <Button level="danger" onClick={open} size="default">
        {translate('pim_datagrid.workflow.actions.refuse_all')}
      </Button>
      {isOpen && <ProposalModal action="reject" onClose={close} getUrl={getUrl} />}
    </>
  );
};

const RemoveAllButton: (props: AllProps) => JSX.Element = ({productDraftType, id}) => {
  const [isOpen, open, close] = useBooleanState();
  const translate = useTranslate();
  const router = useRouter();
  const getUrl = (comment: string) => router.generate(`pimee_workflow_${productDraftType}_rest_remove`, {id, comment});

  return (
    <>
      <Button level="danger" onClick={open} size="default">
        {translate('pim_common.remove')}
      </Button>
      {isOpen && <ProposalModal action="remove" onClose={close} getUrl={getUrl} />}
    </>
  );
};

const ApproveButton: (props: PartialProps) => JSX.Element = ({
  productDraftType,
  id,
  attributeCode,
  attributeLabel,
  documentLabel,
  locale,
  scope,
}) => {
  const [isOpen, open, close] = useBooleanState();
  const translate = useTranslate();
  const router = useRouter();
  const getUrl = (comment: string) =>
    router.generate(`pimee_workflow_${productDraftType}_rest_partial_approve`, {
      id,
      code: attributeCode,
      locale,
      scope,
      comment,
    });

  return (
    <>
      <IconButton
        onClick={open}
        ghost
        icon={<CheckIcon />}
        size="small"
        title={translate('pim_datagrid.workflow.partial_approve', {
          attribute: attributeLabel,
          product: documentLabel,
        })}
      />
      {isOpen && <ProposalModal action="partial_approve" onClose={close} getUrl={getUrl} />}
    </>
  );
};

const RejectButton: (props: PartialProps) => JSX.Element = ({
  productDraftType,
  id,
  attributeCode,
  attributeLabel,
  documentLabel,
  scope,
  locale,
}) => {
  const [isOpen, open, close] = useBooleanState();
  const translate = useTranslate();
  const router = useRouter();
  const getUrl = (comment: string) =>
    router.generate(`pimee_workflow_${productDraftType}_rest_partial_reject`, {
      id,
      code: attributeCode,
      scope,
      locale,
      comment,
    });

  return (
    <>
      <IconButton
        onClick={open}
        ghost
        level="danger"
        icon={<CloseIcon />}
        size="small"
        title={translate('pim_datagrid.workflow.partial_reject', {
          attribute: attributeLabel,
          product: documentLabel,
        })}
      />
      {isOpen && <ProposalModal action="partial_reject" onClose={close} getUrl={getUrl} />}
    </>
  );
};
export {ApproveAllButton, RejectAllButton, RemoveAllButton, ApproveButton, RejectButton};
