import React, {useCallback, useRef, useState} from 'react';
import {AssetsIllustration, Checkbox, Button, Modal, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath, Section, TextField, ValidationError} from '@akeneo-pim-community/shared';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {sanitizeAssetCode} from 'akeneoassetmanager/tools/sanitizeAssetCode';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';

const submitCreateAsset = async (
  code: AssetCode,
  label: string,
  localeCode: LocaleCode,
  assetFamilyIdentifier: AssetFamilyIdentifier,
  onSuccess: () => void,
  onFailure: (errors: ValidationError[]) => void
) => {
  const asset = {
    code,
    assetFamilyIdentifier,
    labels: {[localeCode]: label},
    values: [],
  };

  try {
    let errors = await assetSaver.create(asset);
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
  code: AssetCode,
  label: string,
  locale: LocaleCode,
  assetFamilyIdentifier: AssetFamilyIdentifier,
  onSuccess: () => void,
  onFailure: (errors: ValidationError[]) => void
) => {
  const [isCreating, setCreating] = useState(false);

  return useCallback(() => {
    if (isCreating) return;
    setCreating(true);
    submitCreateAsset(
      code,
      label,
      locale,
      assetFamilyIdentifier,
      () => {
        setCreating(false);
        onSuccess();
      },
      (errors: ValidationError[]) => {
        setCreating(false);
        onFailure(errors);
      }
    );
  }, [code, label, locale, assetFamilyIdentifier, onSuccess, onFailure, isCreating]);
};

type CreateModalProps = {
  assetFamily: AssetFamily;
  locale: LocaleCode;
  onClose: () => void;
  onAssetCreated: (assetCode: AssetCode, createAnother: boolean) => void;
};

const CreateModal = ({assetFamily, locale, onClose, onAssetCreated}: CreateModalProps) => {
  const translate = useTranslate();
  const [code, setCode] = useState<AssetCode>('');
  const [label, setLabel] = useState<string>('');
  const [createAnother, setCreateAnother] = useState<boolean>(false);
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleLabelChange = useCallback(
    (newLabel: string) => {
      const expectedSanitizedCode = sanitizeAssetCode(label);
      const newCode = expectedSanitizedCode === code ? sanitizeAssetCode(newLabel) : code;
      setCode(newCode);
      setLabel(newLabel);
    },
    [code, label]
  );

  const resetModal = useCallback(() => {
    setCode('');
    setLabel('');
    setErrors([]);
    setFocus();
  }, []);

  const onSuccess = useCallback(() => {
    onAssetCreated(code, createAnother);

    if (createAnother) {
      resetModal();
    }
  }, [code, createAnother]);

  const submit = useSubmit(code, label, locale, assetFamily.identifier, onSuccess, setErrors);

  const setFocus = useAutoFocus(inputRef);

  return (
    <Modal illustration={<AssetsIllustration />} onClose={onClose} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">{translate('pim_asset_manager.asset.create.subtitle')}</Modal.SectionTitle>
      <Modal.Title>
        {translate('pim_asset_manager.asset.create.title', {
          entityLabel: getAssetFamilyLabel(assetFamily, locale).toLowerCase(),
        })}
      </Modal.Title>
      <Section>
        <TextField
          locale={locale}
          ref={inputRef}
          label={translate('pim_asset_manager.asset.create.input.label')}
          value={label}
          onChange={handleLabelChange}
          errors={getErrorsForPath(errors, 'labels')}
          onSubmit={submit}
        />
        <TextField
          label={translate('pim_asset_manager.asset.create.input.code')}
          value={code}
          onChange={setCode}
          errors={getErrorsForPath(errors, 'code')}
          onSubmit={submit}
        />
        <Checkbox checked={createAnother} onChange={setCreateAnother}>
          {translate('pim_asset_manager.asset.create.input.create_another')}
        </Checkbox>
      </Section>
      <Modal.BottomButtons>
        <Button onClick={submit}>{translate('pim_common.save')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateModal};
