import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoreferenceentity/tools/translator';
import {getLabel} from 'pimui/js/i18n';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {
  optionEditionStart,
  optionEditionCancel,
  optionEditionSubmission,
  optionEditionCodeUpdated,
  optionEditionSelected,
  optionEditionLabelUpdated,
  optionEditionDelete,
} from 'akeneoreferenceentity/domain/event/attribute/option';
import Key from 'akeneoreferenceentity/tools/key';
import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import hydrateAttribute from 'akeneoreferenceentity/application/hydrator/attribute';
import {AttributeWithOptions} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import LocaleSwitcher from 'akeneoreferenceentity/application/component/app/locale-switcher';
import {StructureState} from 'akeneoreferenceentity/application/reducer/structure';
import {catalogLocaleChanged} from 'akeneoreferenceentity/domain/event/user';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import {saveOptions} from 'akeneoreferenceentity/application/action/attribute/edit';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import Close from 'akeneoreferenceentity/application/component/app/icon/close';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {NormalizedReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';

const OptionView = ({onOptionEditionStart}: {onOptionEditionStart: () => void}) => {
  return (
    <div className="AknFieldContainer AknFieldContainer--packed">
      <div className="AknFieldContainer-header">
        <button onClick={onOptionEditionStart} className="AknButton" data-code="manageOption">
          {__('pim_reference_entity.attribute.edit.input.manage_options.quick_edit.label')}
        </button>
      </div>
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
  options: NormalizedOption[];
  currentOptionId: number;
  isActive: boolean;
  isDirty: boolean;
  attribute: NormalizedAttribute;
  errors: ValidationError[];
  locale: string;
  structure: StructureState;
  referenceEntity: NormalizedReferenceEntity;
  catalogLocale: string;
  numberOfLockedOptions: any;
};

enum Field {
  Code,
  Label,
}

interface ManageOptionsProps extends StateProps, DispatchProps {}

const optionRow = ({
  code,
  label,
  index,
  isLastRow,
  numberOfLockedOptions,
  locale,
  errors,
  labelInputReference,
  codeInputReference,
  onOptionEditionCodeUpdated,
  onOptionEditionSelected,
  onOptionEditionLabelUpdated,
  onOptionEditionDelete,
  onFocusNextField,
  onFocusPreviousField,
}: {
  code: string;
  label: string;
  index: number;
  isLastRow: boolean;
  numberOfLockedOptions: any;
  locale: string;
  errors: ValidationError[];
  labelInputReference: React.RefObject<HTMLInputElement>;
  codeInputReference: React.RefObject<HTMLInputElement>;
  onOptionEditionCodeUpdated: (code: string, id: any) => void;
  onOptionEditionSelected: (id: any) => void;
  onOptionEditionLabelUpdated: (label: string, locale: string, id: any) => void;
  onOptionEditionDelete: (id: any) => void;
  onFocusNextField: (index: number, field: Field) => void;
  onFocusPreviousField: (index: number, field: Field) => void;
}) => {
  return (
    <React.Fragment key={index}>
      <tr data-code={code} className="AknOptionEditor-row">
        <td>
          <div className="AknFieldContainer">
            <div className="AknFieldContainer-inputContainer">
              <input
                ref={labelInputReference}
                placeholder={
                  isLastRow
                    ? __('pim_reference_entity.attribute.edit.input.manage_options.option.label.placeholder')
                    : ''
                }
                type="text"
                className="AknTextField AknTextField--light"
                id={`pim_reference_entity.attribute.edit.input.${code}_${index}.label`}
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
              />
            </div>
            {!isLastRow ? getErrorsView(errors, `options.${index}`) : null}
          </div>
        </td>
        <td>
          <div className="AknFieldContainer">
            <div className="AknFieldContainer-inputContainer">
              <input
                ref={codeInputReference}
                type="text"
                className={
                  'AknTextField AknTextField--light' +
                  (index <= numberOfLockedOptions - 1 ? ' AknTextField--disabled' : '')
                }
                tabIndex={index <= numberOfLockedOptions - 1 ? -1 : 0}
                id={`pim_reference_entity.attribute.edit.input.${code}_${index}.code`}
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
              />
            </div>
          </div>
        </td>
        <td>
          {!isLastRow ? (
            <Close
              onClick={() => onOptionEditionDelete(index)}
              onKeyPress={(event: React.KeyboardEvent<SVGElement>) => {
                if (Key.Space === event.key) onOptionEditionDelete(index);
              }}
              color="#67768A"
              className="AknOptionEditor-remove"
              tabIndex={0}
            />
          ) : null}
        </td>
      </tr>
    </React.Fragment>
  );
};

const helperRow = ({locale, currentOption}: {locale: Locale; currentOption: NormalizedOption}) => {
  const label = currentOption.labels[locale.code] ? currentOption.labels[locale.code] : '';

  return (
    <React.Fragment key={locale.code}>
      <div className="AknFieldContainer">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label">{locale.label}</label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            className="AknTextField AknTextField--light AknTextField--disabled"
            value={label}
            readOnly
            tabIndex={-1}
          />
          <Flag locale={locale} displayLanguage={false} className="AknFieldContainer-inputSides" />
        </div>
      </div>
    </React.Fragment>
  );
};

class ManageOptionsView extends React.Component<ManageOptionsProps> {
  /**
   * We keep track of the references of the input in order to put the user in the
   * right input whenever he presses enter.
   *
   * Ex: The user has the focus on the code red, when he presses enter the focus will go in the next code input.
   * (It works the same for the labels)
   */
  private labelInputReferences: React.RefObject<HTMLInputElement>[] = [];
  private codeInputReferences: React.RefObject<HTMLInputElement>[] = [];

  componentDidMount() {
    const current = this.labelInputReferences[0].current;
    if (null !== current) {
      current.focus();
    }
  }

  updateRefs(options: NormalizedOption[]) {
    this.labelInputReferences = [
      ...options.map(() => React.createRef<HTMLInputElement>()),
      React.createRef<HTMLInputElement>(),
    ];
    this.codeInputReferences = [
      ...options.map(() => React.createRef<HTMLInputElement>()),
      React.createRef<HTMLInputElement>(),
    ];
  }

  cancelManageOptions() {
    const message = __('pim_enrich.confirmation.discard_changes', {entity: 'options'});
    if (this.props.isDirty) {
      if (confirm(message)) {
        this.props.events.onOptionEditionCancel();
      }
    } else {
      this.props.events.onOptionEditionCancel();
    }
  }

  onFocusNextField(index: number, field: Field) {
    const newIndex = index === this.props.options.length ? this.props.options.length : index + 1;
    if (field === Field.Code) {
      const codeReference = this.codeInputReferences[newIndex].current;
      if (null !== codeReference) {
        codeReference.focus();
      }
    }
    if (field === Field.Label) {
      const codeReference = this.labelInputReferences[newIndex].current;
      if (null !== codeReference) {
        codeReference.focus();
      }
    }
  }

  onFocusPreviousField(index: number, field: Field) {
    const newIndex = index === 0 ? 0 : index - 1;
    if (field === Field.Code) {
      const ref = this.codeInputReferences[newIndex].current;
      if (null !== ref) {
        ref.focus();
      }
    }
    if (field === Field.Label) {
      const ref = this.labelInputReferences[newIndex].current;
      if (null !== ref) {
        ref.focus();
      }
    }
  }

  render() {
    const options = [...this.props.options, Option.createEmpty().normalize()];
    const defaultCatalogLocale = this.props.structure.locales.filter(
      locale => locale.code === this.props.catalogLocale
    );
    const localesWithoutDefaultCatalogLocale = this.props.structure.locales.filter(
      locale => locale.code !== this.props.catalogLocale
    );
    const sortedLocales = [...defaultCatalogLocale, ...localesWithoutDefaultCatalogLocale];

    this.updateRefs(options);

    return (
      <React.Fragment>
        {this.props.isActive ? (
          <div className="modal in modal--fullPage manageOptionModal" aria-hidden="false" style={{zIndex: 1041}}>
            <div className="AknFullPage-content AknFullPage-content--column">
              <div>
                <div className="AknFullPage-subTitle">
                  {__('pim_reference_entity.attribute.options.sub_title')} / {this.props.referenceEntity.code}
                </div>
                <div className="AknFullPage-title">
                  {__('pim_reference_entity.attribute.edit.input.manage_options.quick_edit.label')}
                </div>
              </div>
            </div>
            <div>
              <div className="AknFullPage AknFullPage--modal">
                <div className="AknFullPage-content AknFullPage-content--visible">
                  <div className="AknOptionEditor">
                    <div className="AknSubsection AknOptionEditor-translator">
                      <div className="AknSubsection-title AknSubsection-title--sticky AknSubsection-title--light">
                        <span className="AknSubsection-titleLabel">
                          {getLabel(this.props.attribute.labels, this.props.locale, this.props.attribute.code)}
                        </span>
                        <LocaleSwitcher
                          localeCode={this.props.locale}
                          locales={this.props.structure.locales}
                          onLocaleChange={this.props.events.onLocaleChanged}
                        />
                      </div>
                      <table className="AknOptionEditor-table">
                        <thead>
                          <tr>
                            <th className="AknOptionEditor-headCell">
                              <label className="AknOptionEditor-headCellLabel">{__('pim_common.label')}</label>
                            </th>
                            <th className="AknOptionEditor-headCell">
                              <label className="AknOptionEditor-headCellLabel">
                                {__('pim_reference_entity.attribute.edit.input.code')}
                              </label>
                            </th>
                            <th className="AknOptionEditor-headCell">
                              <label className="AknOptionEditor-headCellLabel" />
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          {options.map((option: NormalizedOption, index: number) => {
                            return optionRow({
                              code: option.code,
                              label: option.labels[this.props.locale],
                              index: index,
                              isLastRow: index >= options.length - 1,
                              numberOfLockedOptions: this.props.numberOfLockedOptions,
                              locale: this.props.locale,
                              errors: this.props.errors,
                              labelInputReference: this.labelInputReferences[index],
                              codeInputReference: this.codeInputReferences[index],
                              onOptionEditionCodeUpdated: this.props.events.onOptionEditionCodeUpdated,
                              onOptionEditionSelected: this.props.events.onOptionEditionSelected,
                              onOptionEditionLabelUpdated: this.props.events.onOptionEditionLabelUpdated,
                              onOptionEditionDelete: this.props.events.onOptionEditionDelete,
                              onFocusNextField: this.onFocusNextField.bind(this),
                              onFocusPreviousField: this.onFocusPreviousField.bind(this),
                            });
                          })}
                          <tr>
                            <td>{getErrorsView(this.props.errors, 'options')}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div className="AknOptionEditor-helper">
                      <div className="AknSubsection-title AknSubsection-title--light">
                        <span className="AknSubsection-titleLabel">
                          {__('pim_reference_entity.attribute.options.helper.title')}
                        </span>
                      </div>
                      <div className="AknOptionEditor-labelList">
                        {sortedLocales.map((locale: Locale) => {
                          if (locale.code === this.props.locale) {
                            return;
                          }

                          return helperRow({
                            locale: locale,
                            currentOption: options[this.props.currentOptionId],
                          });
                        })}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div className="AknButtonList AknButtonList--right modal-footer">
              <button
                className="AknButtonList-item AknButton AknButton--apply ok icons-holder-text confirm"
                onClick={this.props.events.onOptionEditionSubmission}
              >
                {__('pim_reference_entity.attribute.create.confirm')}
              </button>
              <span
                title={__('pim_reference_entity.attribute.create.cancel')}
                className="AknButtonList-item AknButton AknButton--grey cancel icons-holder-text"
                onClick={this.cancelManageOptions.bind(this)}
                tabIndex={0}
                onKeyPress={event => {
                  if (Key.Space === event.key) this.cancelManageOptions();
                }}
              >
                {__('pim_reference_entity.attribute.create.cancel')}
              </span>
            </div>
          </div>
        ) : null}
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState) => {
    return {
      ...state.options,
      locale: state.user.catalogLocale,
      structure: state.structure,
      attribute: state.attribute.data,
      isDirty: state.options.isDirty,
      numberOfLockedOptions: state.options.numberOfLockedOptions,
      referenceEntity: state.form.data,
      catalogLocale: state.user.defaultCatalogLocale,
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
