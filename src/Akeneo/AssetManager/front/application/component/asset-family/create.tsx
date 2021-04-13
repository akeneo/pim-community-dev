import React, {useCallback, useRef, useState} from 'react';
import {AssetsIllustration, Button, Modal, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath, Section, TextField, ValidationError} from '@akeneo-pim-community/shared';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import sanitize from 'akeneoassetmanager/tools/sanitize';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import assetFamilySaver from 'akeneoassetmanager/infrastructure/saver/asset-family';

const submitCreateAssetFamily = async (
  code: AssetFamilyIdentifier,
  label: string,
  localeCode: LocaleCode,
  onSuccess: () => void,
  onFailure: (errors: ValidationError[]) => void
) => {
  const assetFamily = {
    code,
    labels: {[localeCode]: label},
  };

  try {
    let errors = await assetFamilySaver.create(assetFamily);
    if (errors) {
      onFailure(errors);
      return;
    }
  } catch (error) {
    onFailure([]);
    return;
  }

  onSuccess();
};

const useSubmit = (
  code: AssetFamilyIdentifier,
  label: string,
  locale: LocaleCode,
  onSuccess: () => void,
  onFailure: (errors: ValidationError[]) => void
) => {
  const [isCreating, setCreating] = useState(false);

  return useCallback(() => {
    if (isCreating) return;
    setCreating(true);
    submitCreateAssetFamily(
      code,
      label,
      locale,
      () => {
        setCreating(false);
        onSuccess();
      },
      (errors: ValidationError[]) => {
        setCreating(false);
        onFailure(errors);
      }
    );
  }, [code, label, locale, onSuccess, onFailure, isCreating]);
};

type CreateAssetFamilyModalProps = {
  locale: LocaleCode;
  onClose: () => void;
  onAssetFamilyCreated: (code: AssetFamilyIdentifier) => void;
};

const CreateAssetFamilyModal = ({locale, onClose, onAssetFamilyCreated}: CreateAssetFamilyModalProps) => {
  const translate = useTranslate();
  const [code, setCode] = useState<AssetFamilyIdentifier>('');
  const [label, setLabel] = useState<string>('');
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleLabelChange = useCallback(
    (newLabel: string) => {
      const expectedSanitizedCode = sanitize(label);
      const newCode = expectedSanitizedCode === code ? sanitize(newLabel) : code;
      setCode(newCode);
      setLabel(newLabel);
    },
    [code, label]
  );

  const onSuccess = useCallback(() => {
    onAssetFamilyCreated(code);
  }, [code]);

  const submit = useSubmit(code, label, locale, onSuccess, setErrors);

  useAutoFocus(inputRef);

  return (
    <Modal illustration={<AssetsIllustration />} onClose={onClose} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">
        {translate('pim_asset_manager.asset_family.create.subtitle')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_asset_manager.asset_family.create.title')}</Modal.Title>
      <Section>
        <TextField
          locale={locale}
          ref={inputRef}
          label={translate('pim_asset_manager.asset_family.create.input.label')}
          value={label}
          onChange={handleLabelChange}
          errors={getErrorsForPath(errors, 'labels')}
          onSubmit={submit}
        />
        <TextField
          label={translate('pim_asset_manager.asset_family.create.input.code')}
          value={code}
          onChange={setCode}
          errors={getErrorsForPath(errors, 'identifier')}
          onSubmit={submit}
        />
      </Section>
      <Modal.BottomButtons>
        <Button onClick={submit}>{translate('pim_common.save')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateAssetFamilyModal};
