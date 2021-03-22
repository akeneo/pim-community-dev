import React, { useState } from "react";
import { Button, Field, Modal, ProposalsIllustration, TextAreaInput } from "akeneo-design-system";
import { NotificationLevel, useNotify, useTranslate } from "@akeneo-pim-community/legacy-bridge";

const ProposalModal = ({action, url, onClose, titleParams}: {
  action: 'approve' | 'reject' | 'partial_approve' | 'partial_reject',
  url: (comment: string) => string,
  onClose: () => void,
  titleParams?: any,
}) => {
  const translate = useTranslate();
  const notify = useNotify();
  const [ comment, setComment ] = useState<string>('');

  const handleSend = async () => {
    fetch(url(comment), {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    })
      .then((response) => {
        notify(NotificationLevel.SUCCESS, translate(`pimee_workflow.proposal.${action}.success`));
        /**
         * Hard reload of the page, if deleted the last grid proposal,
         * in order to refresh proposal grid filters.
          if (1 === $('table.proposal-changes').length) {
            window.location.reload();
          } else {
            mediator.trigger('datagrid:doRefresh:' + gridName);
          }
         */
      })
      .catch((error) => {
        notify(NotificationLevel.ERROR, translate(`pimee_workflow.proposal.${action}.error`, { error }));
      })
      .finally(() => {
        onClose();
      });
  }

  return (
    <Modal
      onClose={onClose}
      closeTitle={translate('pim_common.close', titleParams || {})}
      illustration={<ProposalsIllustration/>}
     >
       <Modal.Title>{translate(`pimee_workflow.proposal.${action}.title`)}</Modal.Title>
       <Field label={translate('pimee_workflow.entity.proposal.modal.title')}>
         <TextAreaInput
           value={comment}
           maxLength={255}
           characterLeftLabel={translate('pim_datagrid.workflow.characters_left', { count: 255 - comment.length }, 255 - comment.length )}
           onChange={setComment}
         />
       </Field>
       <Modal.BottomButtons>
         <Button onClick={onClose} level="tertiary">{translate('pim_common.cancel')}</Button>
         <Button onClick={handleSend} level="primary">{translate('pimee_enrich.entity.product_draft.module.proposal.confirm')}</Button>
       </Modal.BottomButtons>
     </Modal>
  );
}

export { ProposalModal };
