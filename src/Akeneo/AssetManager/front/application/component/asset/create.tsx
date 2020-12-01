import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createLocaleFromCode, LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
import {AssetsIllustration} from 'akeneo-design-system';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {sanitizeAssetCode} from 'akeneoassetmanager/tools/sanitizeAssetCode';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';
import {useFocus, useShortcut} from 'akeneoassetmanager/application/hooks/input';
import Key from 'akeneoassetmanager/tools/key';

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

export const useSubmit = (
  code: AssetCode,
  label: string,
  locale: LocaleCode,
  assetFamilyIdentifier: AssetFamilyIdentifier,
  onSuccess: () => void,
  onFailure: (errors: ValidationError[]) => void
) => {
  const [isCreating, setCreating] = React.useState(false);
  return React.useCallback(() => {
    if (isCreating) return;
    setCreating(true);
    submitCreateAsset(
      code,
      label,
      locale,
      assetFamilyIdentifier,
      () => {
        onSuccess();
        setCreating(false);
      },
      (errors: ValidationError[]) => {
        onFailure(errors);
        setCreating(false);
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

export const CreateModal = ({assetFamily, locale, onClose, onAssetCreated}: CreateModalProps) => {
  const [code, setCode] = React.useState<AssetCode>('');
  const [label, setLabel] = React.useState<string>('');
  const [createAnother, setCreateAnother] = React.useState<boolean>(false);
  const [errors, setErrors] = React.useState<ValidationError[]>([]);

  const onLabelUpdate = React.useCallback(
    (newLabel: string) => {
      const expectedSanitizedCode = sanitizeAssetCode(label);
      const newCode = expectedSanitizedCode === code ? sanitizeAssetCode(newLabel) : code;
      setCode(newCode);
      setLabel(newLabel);
    },
    [code, label]
  );

  const resetModal = React.useCallback(() => {
    setCode('');
    setLabel('');
    setErrors([]);
    setFocus();
  }, []);

  const onSuccess = React.useCallback(() => {
    onAssetCreated(code, createAnother);
    resetModal();
  }, [code, createAnother]);

  const submit = useSubmit(code, label, locale, assetFamily.identifier, onSuccess, setErrors);

  useShortcut(Key.Enter, submit);
  useShortcut(Key.Escape, onClose);
  const [inputRef, setFocus] = useFocus();

  return (
    <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
      <div className="modal-body  creation">
        <div className="AknFullPage">
          <div className="AknFullPage-content AknFullPage-content--withIllustration">
            <div>
              <AssetsIllustration />
            </div>
            <div>
              <div className="AknFormContainer">
                <div className="AknFullPage-titleContainer">
                  <div className="AknFullPage-subTitle">{__('pim_asset_manager.asset.create.subtitle')}</div>
                  <div className="AknFullPage-title">
                    {__('pim_asset_manager.asset.create.title', {
                      entityLabel: getAssetFamilyLabel(assetFamily, locale).toLowerCase(),
                    })}
                  </div>
                </div>
                <div className="AknFieldContainer" data-code="label">
                  <div className="AknFieldContainer-header AknFieldContainer-header--light">
                    <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.asset.create.input.label">
                      {__('pim_asset_manager.asset.create.input.label')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      ref={inputRef}
                      autoComplete="off"
                      type="text"
                      className="AknTextField AknTextField--light"
                      id="pim_asset_manager.asset.create.input.label"
                      name="label"
                      value={label}
                      onChange={event => onLabelUpdate(event.target.value)}
                    />
                    <Flag
                      locale={createLocaleFromCode(locale)}
                      displayLanguage={false}
                      className="AknFieldContainer-inputSides"
                    />
                  </div>
                  {getErrorsView(errors, 'labels')}
                </div>
                <div className="AknFieldContainer" data-code="code">
                  <div className="AknFieldContainer-header AknFieldContainer-header--light">
                    <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.asset.create.input.code">
                      {__('pim_asset_manager.asset.create.input.code')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      type="text"
                      autoComplete="off"
                      className="AknTextField AknTextField--light"
                      id="pim_asset_manager.asset.create.input.code"
                      name="code"
                      value={code}
                      onChange={event => setCode(event.target.value)}
                    />
                  </div>
                  {getErrorsView(errors, 'code')}
                </div>
                <div className="AknFieldContainer" data-code="create_another">
                  <div className="AknFieldContainer-header AknFieldContainer-header--light">
                    <label
                      className="AknFieldContainer-label"
                      htmlFor="pim_asset_manager.asset.create.input.create_another"
                    >
                      <Checkbox
                        id="pim_asset_manager.asset.create.input.create_another"
                        value={createAnother}
                        onChange={(newValue: boolean) => setCreateAnother(newValue)}
                      />
                      <span onClick={() => setCreateAnother(!createAnother)}>
                        {__('pim_asset_manager.asset.create.input.create_another')}
                      </span>
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer" />
                </div>
                <button className="AknButton AknButton--apply ok" onClick={submit}>
                  {__('pim_asset_manager.asset.create.confirm')}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        title={__('pim_asset_manager.asset.create.cancel')}
        className="AknFullPage-cancel cancel"
        onClick={onClose}
      />
    </div>
  );
};
