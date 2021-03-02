import React, {RefObject, createRef} from 'react';
import {connect} from 'react-redux';
import {Button, Key, CloseIcon} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  optionEditionCancel,
  optionEditionCodeUpdated,
  optionEditionDelete,
  optionEditionLabelUpdated,
  optionEditionSelected,
  optionEditionStart,
  optionEditionSubmission,
} from 'akeneoassetmanager/domain/event/attribute/option';
import {Option, createEmptyOption} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import hydrateAttribute from 'akeneoassetmanager/application/hydrator/attribute';
import {AttributeWithOptions} from 'akeneoassetmanager/domain/model/attribute/type/option';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import {catalogLocaleChanged} from 'akeneoassetmanager/domain/event/user';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {saveOptions} from 'akeneoassetmanager/application/action/attribute/edit';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import Flag from 'akeneoassetmanager/tools/component/flag';
import AssetFamilyCode from 'akeneoassetmanager/domain/model/asset-family/code';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const securityContext = require('pim/security-context');

const OptionView = ({onOptionEditionStart}: {onOptionEditionStart: () => void}) => {
  const translate = useTranslate();

  return (
    <div>
      <Button onClick={onOptionEditionStart} level="tertiary" ghost={true} data-code="manageOption">
        {translate('pim_asset_manager.attribute.edit.input.manage_options.quick_edit.label')}
      </Button>
    </div>
  );
};

export const view = connect(
  () => ({}),
  (dispatch: any) => ({
    onOptionEditionStart: () => {
      dispatch((dispatch: any, getState: () => EditState) => {
        const attribute = hydrateAttribute(getState().attribute.data);
        dispatch(optionEditionStart(((attribute as any) as AttributeWithOptions).getOptions()));
      });
    },
  })
)(OptionView);

type DispatchProps = {
  events: {
    onOptionEditionCancel: () => void;
    onOptionEditionCodeUpdated: (code: string, id: any) => void;
    onOptionEditionSelected: (id: any) => void;
    onOptionEditionLabelUpdated: (label: string, locale: string, id: any) => void;
    onOptionEditionSubmission: (id: any) => void;
    onOptionEditionDelete: (id: any) => void;
    onLocaleChanged: (locale: Locale) => void;
  };
};

type StateProps = {
  options: Option[];
  currentOptionId: number;
  isActive: boolean;
  isDirty: boolean;
  attribute: NormalizedAttribute;
  errors: ValidationError[];
  locale: string;
  structure: {
    locales: Locale[];
  };
  assetFamilyCode: AssetFamilyCode;
  catalogLocale: string;
  numberOfLockedOptions: any;
};

type OwnProps = {
  rights: {
    locale: {
      edit: boolean;
    };
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
};

enum Field {
  Code,
  Label,
}

interface ManageOptionsProps extends StateProps, OwnProps, DispatchProps {}

type OptionRowProps = {
  code: string;
  label: string;
  index: number;
  isLastRow: boolean;
  numberOfLockedOptions: any;
  locale: string;
  errors: ValidationError[];
  rights: {
    locale: {
      edit: boolean;
    };
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
  labelInputReference: React.RefObject<HTMLInputElement>;
  codeInputReference: React.RefObject<HTMLInputElement>;
  onOptionEditionCodeUpdated: (code: string, id: any) => void;
  onOptionEditionSelected: (id: any) => void;
  onOptionEditionLabelUpdated: (label: string, locale: string, id: any) => void;
  onOptionEditionDelete: (id: any) => void;
  onFocusNextField: (index: number, field: Field) => void;
  onFocusPreviousField: (index: number, field: Field) => void;
};

const OptionRow = ({
  code,
  label,
  index,
  isLastRow,
  numberOfLockedOptions,
  locale,
  errors,
  rights,
  labelInputReference,
  codeInputReference,
  onOptionEditionCodeUpdated,
  onOptionEditionSelected,
  onOptionEditionLabelUpdated,
  onOptionEditionDelete,
  onFocusNextField,
  onFocusPreviousField,
}: OptionRowProps) => {
  const translate = useTranslate();
  const displayDeleteRowButton: boolean = !isLastRow && rights.attribute.delete;
  const canEditLabel = rights.attribute.edit && rights.locale.edit;
  const labelClassName = `AknTextField AknTextField--light ${!canEditLabel ? 'AknTextField--disabled' : ''}`;

  return (
    <>
      {!isLastRow || rights.attribute.edit ? (
        <tr data-code={code} className="AknOptionEditor-row">
          <td>
            <div>
              <div className="AknFieldContainer-inputContainer">
                <input
                  autoComplete="off"
                  ref={labelInputReference}
                  placeholder={
                    isLastRow && canEditLabel
                      ? translate('pim_asset_manager.attribute.edit.input.manage_options.option.label.placeholder')
                      : ''
                  }
                  type="text"
                  className={labelClassName}
                  id={`pim_asset_manager.attribute.edit.input.${code}_${index}.label`}
                  name="label"
                  value={undefined === label ? '' : label}
                  onFocus={() => {
                    onOptionEditionSelected(index);
                  }}
                  onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
                    onOptionEditionLabelUpdated(event.currentTarget.value, locale, index);
                  }}
                  onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
                    if (Key.Enter === event.key) {
                      if (event.shiftKey) {
                        onFocusPreviousField(index, Field.Label);
                      } else {
                        onFocusNextField(index, Field.Label);
                      }
                    }
                  }}
                  readOnly={!canEditLabel}
                />
              </div>
              {!isLastRow ? getErrorsView(errors, `options.${index}`) : null}
            </div>
          </td>
          <td>
            <div>
              <div className="AknFieldContainer-inputContainer">
                <input
                  ref={codeInputReference}
                  autoComplete="off"
                  type="text"
                  className={
                    'AknTextField AknTextField--light' +
                    (index <= numberOfLockedOptions - 1 && !rights.attribute.edit ? ' AknTextField--disabled' : '')
                  }
                  tabIndex={index <= numberOfLockedOptions - 1 ? -1 : 0}
                  id={`pim_asset_manager.attribute.edit.input.${code}_${index}.code`}
                  name="code"
                  value={undefined === code ? '' : code}
                  onFocus={() => {
                    onOptionEditionSelected(index);
                  }}
                  onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
                    onOptionEditionCodeUpdated(event.currentTarget.value, index);
                  }}
                  onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
                    if (Key.Enter === event.key) {
                      if (event.shiftKey) {
                        onFocusPreviousField(index, Field.Code);
                      } else {
                        onFocusNextField(index, Field.Code);
                      }
                    }
                  }}
                  readOnly={!rights.attribute.edit}
                />
              </div>
            </div>
          </td>
          <td>
            {displayDeleteRowButton ? (
              <CloseIcon
                onClick={() => onOptionEditionDelete(index)}
                onKeyPress={(event: React.KeyboardEvent<SVGElement>) => {
                  if (Key.Space === event.key) onOptionEditionDelete(index);
                }}
                className="AknOptionEditor-remove"
                tabIndex={0}
              />
            ) : null}
          </td>
        </tr>
      ) : null}
    </>
  );
};

const HelperRow = ({locale, currentOption}: {locale: Locale; currentOption: Option}) => {
  const label = currentOption.labels[locale.code] ? currentOption.labels[locale.code] : '';

  return (
    <>
      <div>
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label">{locale.label}</label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className="AknTextField AknTextField--light AknTextField--disabled"
            value={label}
            readOnly
            tabIndex={-1}
          />
          <Flag locale={locale} displayLanguage={false} className="AknFieldContainer-inputSides" />
        </div>
      </div>
    </>
  );
};

const ManageOptionsView = ({
  options,
  isDirty,
  structure,
  catalogLocale,
  events,
  assetFamilyCode,
  locale,
  attribute,
  isActive,
  numberOfLockedOptions,
  errors,
  rights,
  currentOptionId,
}: ManageOptionsProps) => {
  const translate = useTranslate();
  const labelInputReferences: RefObject<HTMLInputElement>[] = [
    ...options.map(() => createRef<HTMLInputElement>()),
    React.createRef<HTMLInputElement>(),
  ];
  const codeInputReferences: RefObject<HTMLInputElement>[] = [
    ...options.map(() => createRef<HTMLInputElement>()),
    React.createRef<HTMLInputElement>(),
  ];

  const cancel = () => {
    const message = translate('pim_enrich.confirmation.discard_changes', {entity: 'options'});
    if (isDirty) {
      if (confirm(message)) {
        events.onOptionEditionCancel();
      }
    } else {
      events.onOptionEditionCancel();
    }
  };

  const onFocusNextField = (index: number, field: Field) => {
    const newIndex = index === options.length ? options.length : index + 1;
    if (field === Field.Code) {
      const codeReference = codeInputReferences[newIndex]?.current;
      if (codeReference) codeReference.focus();
    }
    if (field === Field.Label) {
      const codeReference = labelInputReferences[newIndex]?.current;
      if (codeReference) codeReference.focus();
    }
  };

  const onFocusPreviousField = (index: number, field: Field) => {
    const newIndex = index === 0 ? 0 : index - 1;
    if (field === Field.Code) {
      const ref = codeInputReferences[newIndex]?.current;
      if (null !== ref) ref.focus();
    }
    if (field === Field.Label) {
      const ref = labelInputReferences[newIndex]?.current;
      if (null !== ref) ref.focus();
    }
  };
  options = [...options, createEmptyOption()];
  const defaultCatalogLocale = structure.locales.filter(locale => locale.code === catalogLocale);
  const localesWithoutDefaultCatalogLocale = structure.locales.filter(locale => locale.code !== catalogLocale);
  const sortedLocales = [...defaultCatalogLocale, ...localesWithoutDefaultCatalogLocale];

  return (
    <>
      {isActive ? (
        <div className="modal in manageOptionModal" aria-hidden="false" style={{zIndex: 1041}}>
          <div>
            <div className="AknFullPage AknFullPage--full">
              <div className="AknFullPage-content">
                <div className="AknFullPage-titleContainer">
                  <div className="AknFullPage-subTitle">
                    {translate('pim_asset_manager.attribute.options.sub_title')} / {assetFamilyCode}
                  </div>
                  <div className="AknFullPage-title">
                    {translate('pim_asset_manager.attribute.edit.input.manage_options.quick_edit.label')}
                  </div>
                </div>
                <div className="AknOptionEditor">
                  <div className="AknSubsection AknOptionEditor-translator">
                    <div className="AknSubsection-title AknSubsection-title--sticky AknSubsection-title--light">
                      <span className="AknSubsection-titleLabel">
                        {getLabel(attribute.labels, locale, attribute.code)}
                      </span>
                      <LocaleSwitcher
                        localeCode={locale}
                        locales={structure.locales}
                        onLocaleChange={events.onLocaleChanged}
                      />
                    </div>
                    <table className="AknOptionEditor-table">
                      <thead>
                        <tr>
                          <th className="AknOptionEditor-headCell">
                            <label className="AknOptionEditor-headCellLabel">{translate('pim_common.label')}</label>
                          </th>
                          <th className="AknOptionEditor-headCell">
                            <label className="AknOptionEditor-headCellLabel">
                              {translate('pim_asset_manager.attribute.edit.input.code')}
                            </label>
                          </th>
                          <th className="AknOptionEditor-headCell">
                            <label className="AknOptionEditor-headCellLabel" />
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        {options.map((option: Option, index: number) => {
                          return (
                            <OptionRow
                              key={index}
                              code={option.code}
                              label={option.labels[locale]}
                              index={index}
                              isLastRow={index >= options.length - 1}
                              numberOfLockedOptions={numberOfLockedOptions}
                              locale={locale}
                              errors={errors}
                              rights={rights}
                              labelInputReference={labelInputReferences[index]}
                              codeInputReference={codeInputReferences[index]}
                              onOptionEditionCodeUpdated={events.onOptionEditionCodeUpdated}
                              onOptionEditionSelected={events.onOptionEditionSelected}
                              onOptionEditionLabelUpdated={events.onOptionEditionLabelUpdated}
                              onOptionEditionDelete={events.onOptionEditionDelete}
                              onFocusNextField={onFocusNextField.bind(this)}
                              onFocusPreviousField={onFocusPreviousField.bind(this)}
                            />
                          );
                        })}
                        <tr>
                          <td>{getErrorsView(errors, 'options')}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div className="AknOptionEditor-helper">
                    <div className="AknSubsection-title AknSubsection-title--light">
                      <span className="AknSubsection-titleLabel">
                        {translate('pim_asset_manager.attribute.options.helper.title')}
                      </span>
                    </div>
                    <div className="AknOptionEditor-labelList">
                      {sortedLocales.map((currentLocale: Locale) => {
                        if (currentLocale.code === locale) {
                          return;
                        }

                        return (
                          <HelperRow
                            key={currentLocale.code}
                            locale={currentLocale}
                            currentOption={options[currentOptionId]}
                          />
                        );
                      })}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div className="AknButtonList AknButtonList--right modal-footer">
            {rights.attribute.edit ? (
              <Button className="AknFullPage-ok ok confirm" onClick={events.onOptionEditionSubmission}>
                {translate('pim_asset_manager.attribute.create.confirm')}
              </Button>
            ) : null}
            <div
              title={translate('pim_asset_manager.attribute.create.cancel')}
              className="AknFullPage-cancel cancel"
              onClick={cancel}
              tabIndex={0}
              onKeyPress={event => {
                if (Key.Space === event.key) cancel();
              }}
            />
          </div>
        </div>
      ) : null}
    </>
  );
};

export default connect(
  (state: EditState, ownProps: OwnProps) => {
    return {
      ...state.options,
      locale: state.user.catalogLocale,
      structure: {
        locales: state.structure.locales,
      },
      attribute: state.attribute.data,
      isDirty: state.options.isDirty,
      numberOfLockedOptions: state.options.numberOfLockedOptions,
      assetFamilyCode: state.form.data.code,
      catalogLocale: state.user.defaultCatalogLocale,
      rights: {
        locale: {
          edit: ownProps.rights.locale.edit,
        },
        attribute: {
          edit: securityContext.isGranted('akeneo_assetmanager_option_edit') && ownProps.rights.attribute.edit,
          delete:
            securityContext.isGranted('akeneo_assetmanager_option_delete') &&
            securityContext.isGranted('akeneo_assetmanager_option_edit') &&
            ownProps.rights.attribute.edit,
        },
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onOptionEditionCancel: () => {
          dispatch(optionEditionCancel());
        },
        onOptionSubmission: () => {
          dispatch(optionEditionSubmission());
        },
        onOptionEditionCodeUpdated: (code: string, id: any) => {
          dispatch(optionEditionCodeUpdated(code, id));
        },
        onOptionEditionSelected: (id: any) => {
          dispatch(optionEditionSelected(id));
        },
        onOptionEditionLabelUpdated: (label: string, locale: string, id: any) => {
          dispatch(optionEditionLabelUpdated(label, locale, id));
        },
        onLocaleChanged: (locale: Locale) => {
          dispatch(catalogLocaleChanged(locale.code));
        },
        onOptionEditionSubmission: () => {
          dispatch(saveOptions());
        },
        onOptionEditionDelete: (id: any) => {
          dispatch(optionEditionDelete(id));
        },
      },
    } as DispatchProps;
  }
)(ManageOptionsView);
