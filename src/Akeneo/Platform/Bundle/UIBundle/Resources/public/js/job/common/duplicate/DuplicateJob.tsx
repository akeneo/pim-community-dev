import React, {useCallback, useRef, useState} from 'react';
import {Button, ProductsIllustration, Modal, useAutoFocus} from 'akeneo-design-system';
import {
    filterErrors,
    TextField,
    useTranslate,
    Section,
    useRoute,
    NotificationLevel,
    useRouter,
    useNotify,
    ValidationError,
} from '@akeneo-pim-community/shared';

type DuplicateModalProps = {
  title: string;
  subTitle: string;
  jobCodeToDuplicate: string;
  successRedirectRoute: string;
};

type DuplicateJobRequest = {
    label: string;
    code: string;
};

const DuplicateJob = ({
    title,
    subTitle,
    successRedirectRoute,
    jobCodeToDuplicate,
}: DuplicateModalProps) => {
    const translate = useTranslate();
    const notify = useNotify();

    const duplicateRoute = useRoute('pim_enrich_job_instance_rest_duplicate', {code: jobCodeToDuplicate});
    const router = useRouter();
    const [duplicateJobRequest, setDuplicateJobRequest] = useState<DuplicateJobRequest>({label: '', code: ''});
    const [isModalOpen, setModalOpen] = useState<boolean>(false);
    const canDuplicate = '' !== duplicateJobRequest.label && '' !== duplicateJobRequest.code;
    const labelInputRef = useRef(null);
    useAutoFocus(labelInputRef);
    const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);

    const onCancel = () => {
        setDuplicateJobRequest({label: '', code: ''});
        setModalOpen(false);
    };

    const onConfirm = async () => {
        const response = await fetch(
            duplicateRoute,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(duplicateJobRequest),
            }
        );

        if (!response.ok) {
            setValidationErrors([]);

            try {
                const json = await response.json();
                setValidationErrors(json.normalized_errors);
            } catch (error) {}

            notify(NotificationLevel.ERROR, translate('pim_import_export.entity.job_instance.flash.duplicate.fail'));
        } else {
            const json = await response.json();

            setModalOpen(false);
            notify(NotificationLevel.SUCCESS, translate('pim_import_export.entity.job_instance.flash.duplicate.success'));
            router.redirect(router.generate(successRedirectRoute, {code: json.code}));
        }
    };

    const sanitizeCode = (value: string): string => {
        const regex = /[a-zA-Z0-9_]/;

        return value
            .split('')
            .filter((char: string) => char !== ' ')
            .map((char: string) => (char.match(regex) ? char : '_'))
            .join('');
    };

    const handleLabelChange = useCallback(
        (newLabel: string) => {
            const expectedSanitizedCode = sanitizeCode(duplicateJobRequest.label);
            const newCode = expectedSanitizedCode === duplicateJobRequest.code
                ? sanitizeCode(newLabel)
                : duplicateJobRequest.code;
            setDuplicateJobRequest({code: newCode, label: newLabel});
        },
        [duplicateJobRequest]
    );

    return (
        <>
            <div onClick={() => setModalOpen(true)}>{translate('pim_common.duplicate')}</div>
            {isModalOpen && (
                <Modal
                    closeTitle={translate('pim_common.close')}
                    onClose={onCancel}
                    illustration={<ProductsIllustration />}
                >
                    <Modal.SectionTitle color="brand">{translate(subTitle)}</Modal.SectionTitle>
                    <Modal.Title>
                        {translate('pim_import_export.entity.job_instance.duplicate.title', {job_code: jobCodeToDuplicate})}
                    </Modal.Title>
                    <Section>
                        <TextField
                            label={translate('pim_common.label')}
                            value={duplicateJobRequest.label}
                            errors={filterErrors(validationErrors, 'label')}
                            required={true}
                            ref={labelInputRef}
                            onChange={handleLabelChange}
                        />
                        <TextField
                            label={translate('pim_common.code')}
                            value={duplicateJobRequest.code}
                            errors={filterErrors(validationErrors, 'code')}
                            required={true}
                            onChange={code =>
                                setDuplicateJobRequest({...duplicateJobRequest, code})
                            }
                        />
                    </Section>
                    <Modal.BottomButtons>
                        <Button level="tertiary" onClick={onCancel} >
                            {translate('pim_common.cancel')}
                        </Button>
                        <Button level="primary" disabled={!canDuplicate} onClick={onConfirm}>
                            {translate('pim_common.save')}
                        </Button>
                    </Modal.BottomButtons>
                </Modal>)}
        </>
    );
};

export {DuplicateJob};
