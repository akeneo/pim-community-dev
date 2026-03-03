import React, {useRef, useState} from 'react';
import {Button, ProductsIllustration, Modal, useAutoFocus, useBooleanState} from 'akeneo-design-system';
import {
  filterErrors,
  TextField,
  useTranslate,
  sanitize,
  Section,
  NotificationLevel,
  useRouter,
  useNotify,
  ValidationError,
} from '@akeneo-pim-community/shared';

type DuplicateModalProps = {
  subTitle: string;
  jobCodeToDuplicate: string;
  successRedirectRoute: string;
};

const DuplicateJob = ({subTitle, successRedirectRoute, jobCodeToDuplicate}: DuplicateModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const router = useRouter();

  const [label, setLabel] = useState('');
  const [code, setCode] = useState('');
  const labelInputRef = useRef(null);
  const [isModalOpen, openModal, closeModal] = useBooleanState(false);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const canDuplicate = '' !== label && '' !== code;

  useAutoFocus(labelInputRef);

  const onCancel = () => {
    setCode('');
    setLabel('');
    closeModal();
  };

  const onConfirm = async () => {
    const response = await fetch(
      router.generate('pim_enrich_job_instance_rest_duplicate', {code: jobCodeToDuplicate}),
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          code,
          label,
        }),
      }
    );

    setValidationErrors([]);

    if (response.ok) {
      closeModal();

      const json = await response.json();
      notify(NotificationLevel.SUCCESS, translate('pim_import_export.entity.job_instance.duplicate.success'));
      router.redirect(router.generate(successRedirectRoute, {code: json.code}));
    } else {
      try {
        const json = await response.json();
        setValidationErrors(json.values);
      } catch (error) {}

      notify(NotificationLevel.ERROR, translate('pim_import_export.entity.job_instance.duplicate.fail'));
    }
  };

  const handleLabelChange = (newLabel: string) => {
    const expectedSanitizedCode = sanitize(label);
    const newCode = expectedSanitizedCode === code ? sanitize(newLabel) : code;

    setCode(newCode);
    setLabel(newLabel);
  };

  return (
    <>
      <div onClick={openModal}>{translate('pim_common.duplicate')}</div>
      {isModalOpen && (
        <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<ProductsIllustration />}>
          <Modal.SectionTitle color="brand">{translate(subTitle)}</Modal.SectionTitle>
          <Modal.Title>
            {translate('pim_import_export.entity.job_instance.duplicate.title', {job_code: jobCodeToDuplicate})}
          </Modal.Title>
          <Section>
            <TextField
              label={translate('pim_common.label')}
              value={label}
              errors={filterErrors(validationErrors, 'label')}
              required={true}
              ref={labelInputRef}
              onChange={handleLabelChange}
            />
            <TextField
              label={translate('pim_common.code')}
              value={code}
              errors={filterErrors(validationErrors, 'code')}
              required={true}
              onChange={setCode}
            />
          </Section>
          <Modal.BottomButtons>
            <Button level="tertiary" onClick={onCancel}>
              {translate('pim_common.cancel')}
            </Button>
            <Button level="primary" disabled={!canDuplicate} onClick={onConfirm}>
              {translate('pim_common.save')}
            </Button>
          </Modal.BottomButtons>
        </Modal>
      )}
    </>
  );
};

export {DuplicateJob};
