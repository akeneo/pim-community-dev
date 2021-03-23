import React, {useState} from 'react';
import {Button, Field, Modal, ProposalsIllustration, TextAreaInput} from 'akeneo-design-system';
import {NotificationLevel, useMediator, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';

const ProposalModal = ({
  action,
  getUrl,
  onClose,
}: {
  action: 'approve' | 'reject' | 'remove' | 'partial_approve' | 'partial_reject';
  getUrl: (comment: string) => string;
  onClose: (hasSucceed?: boolean) => void;
}) => {
  const translate = useTranslate();
  const notify = useNotify();
  const mediator = useMediator();
  const [comment, setComment] = useState<string>('');

  const handleSend = async () => {
    fetch(getUrl(comment), {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    })
      .then(async response => {
        notify(NotificationLevel.SUCCESS, translate(`pimee_workflow.proposal.${action}.success`));
        /** datagrid name can not be extracted from an Oro cell, so we call refresh on the 3 possible grids */
        ['proposal-grid', 'product-draft-grid', 'product-model-draft-grid'].forEach(gridName =>
          mediator.trigger(`datagrid:doRefresh:${gridName}`)
        );
        onClose(await response.json());
      })
      .catch(error => {
        notify(NotificationLevel.ERROR, translate(`pimee_workflow.proposal.${action}.error`, {error}));
        onClose();
      });
  };

  return (
    <Modal onClose={onClose} closeTitle={translate('pim_common.close')} illustration={<ProposalsIllustration />}>
      <Modal.Title>{translate(`pimee_workflow.proposal.${action}.title`)}</Modal.Title>
      <Field label={translate('pimee_workflow.entity.proposal.modal.title')}>
        <TextAreaInput
          value={comment}
          maxLength={255}
          characterLeftLabel={translate(
            'pim_datagrid.workflow.characters_left',
            {count: 255 - comment.length},
            255 - comment.length
          )}
          onChange={setComment}
        />
      </Field>
      <Modal.BottomButtons>
        <Button
          onClick={() => {
            onClose();
          }}
          level="tertiary"
        >
          {translate('pim_common.cancel')}
        </Button>
        <Button onClick={handleSend} level="primary">
          {translate('pim_common.confirm')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {ProposalModal};
