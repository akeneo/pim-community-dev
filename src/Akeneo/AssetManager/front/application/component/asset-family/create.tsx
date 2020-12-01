import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {createLocaleFromCode, LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {AssetsIllustration} from 'akeneo-design-system';
import sanitize from 'akeneoassetmanager/tools/sanitize';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import assetFamilySaver from 'akeneoassetmanager/infrastructure/saver/asset-family';
import {useFocus, useShortcut} from 'akeneoassetmanager/application/hooks/input';
import Key from 'akeneoassetmanager/tools/key';

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

export const useSubmit = (
  code: AssetFamilyIdentifier,
  label: string,
  locale: LocaleCode,
  onSuccess: () => void,
  onFailure: (errors: ValidationError[]) => void
) => {
  const [isCreating, setCreating] = React.useState(false);
  return React.useCallback(() => {
    if (isCreating) return;
    setCreating(true);
    submitCreateAssetFamily(
      code,
      label,
      locale,
      () => {
        onSuccess();
        setCreating(false);
      },
      (errors: ValidationError[]) => {
        onFailure(errors);
        setCreating(false);
      }
    );
  }, [code, label, locale, onSuccess, onFailure, isCreating]);
};

type CreateAssetFamilyModalProps = {
  locale: LocaleCode;
  onClose: () => void;
  onAssetFamilyCreated: (code: AssetFamilyIdentifier) => void;
};

export const CreateAssetFamilyModal = ({locale, onClose, onAssetFamilyCreated}: CreateAssetFamilyModalProps) => {
  const [code, setCode] = React.useState<AssetFamilyIdentifier>('');
  const [label, setLabel] = React.useState<string>('');
  const [errors, setErrors] = React.useState<ValidationError[]>([]);

  const onLabelUpdate = React.useCallback(
    (newLabel: string) => {
      const expectedSanitizedCode = sanitize(label);
      const newCode = expectedSanitizedCode === code ? sanitize(newLabel) : code;
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
    onAssetFamilyCreated(code);
    resetModal();
  }, [code]);

  const submit = useSubmit(code, label, locale, onSuccess, setErrors);

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
                  <div className="AknFullPage-subTitle">{__('pim_asset_manager.asset_family.create.subtitle')}</div>
                  <div className="AknFullPage-title">{__('pim_asset_manager.asset_family.create.title')}</div>
                </div>
                <div className="AknFieldContainer" data-code="label">
                  <div className="AknFieldContainer-header AknFieldContainer-header--light">
                    <label
                      className="AknFieldContainer-label"
                      htmlFor="pim_asset_manager.asset_family.create.input.label"
                    >
                      {__('pim_asset_manager.asset_family.create.input.label')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      ref={inputRef}
                      autoComplete="off"
                      type="text"
                      className="AknTextField AknTextField--light"
                      id="pim_asset_manager.asset_family.create.input.label"
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
                    <label
                      className="AknFieldContainer-label"
                      htmlFor="pim_asset_manager.asset_family.create.input.code"
                    >
                      {__('pim_asset_manager.asset_family.create.input.code')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      type="text"
                      autoComplete="off"
                      className="AknTextField AknTextField--light"
                      id="pim_asset_manager.asset_family.create.input.code"
                      name="code"
                      value={code}
                      onChange={event => setCode(event.target.value)}
                    />
                  </div>
                  {getErrorsView(errors, 'code')}
                  {getErrorsView(errors, 'identifier')}
                </div>
                <button className="AknButton AknButton--apply ok" onClick={submit}>
                  {__('pim_asset_manager.asset_family.create.confirm')}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        title={__('pim_asset_manager.asset_family.create.cancel')}
        className="AknFullPage-cancel cancel"
        onClick={onClose}
      />
    </div>
  );
};
