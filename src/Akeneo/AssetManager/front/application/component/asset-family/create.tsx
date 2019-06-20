import {createAssetFamily} from 'akeneoassetmanager/application/action/asset-family/create';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {IndexState} from 'akeneoassetmanager/application/reducer/asset-family/index';
import {
  assetFamilyCreationCancel,
  assetFamilyCreationCodeUpdated,
  assetFamilyCreationLabelUpdated,
} from 'akeneoassetmanager/domain/event/asset-family/create';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import Flag from 'akeneoassetmanager/tools/component/flag';
import __ from 'akeneoassetmanager/tools/translator';
import * as React from 'react';
import {connect} from 'react-redux';
import Key from 'akeneoassetmanager/tools/key';
import AssetFamilyCreation, {
  denormalizeAssetFamilyCreation,
} from 'akeneoassetmanager/domain/model/asset-family/creation';

interface StateProps {
  context: {
    locale: string;
  };
  data: AssetFamilyCreation;
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface CreateProps extends StateProps, DispatchProps {}

class Create extends React.Component<CreateProps> {
  private labelInput: HTMLInputElement;
  public props: CreateProps;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  private onCodeUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.onSubmit();
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
        <div>
          <div className="AknFullPage">
            <div className="AknFullPage-content AknFullPage-content--withIllustration">
              <div>
                <img src="bundles/pimui/images/illustrations/Reference-entities.svg" className="AknFullPage-image" />
              </div>
              <div>
                <div className="AknFullPage-titleContainer">
                  <div className="AknFullPage-subTitle">{__('pim_asset_manager.asset_family.create.subtitle')}</div>
                  <div className="AknFullPage-title">{__('pim_asset_manager.asset_family.create.title')}</div>
                </div>
                <div className="AknFormContainer">
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
                        type="text"
                        autoComplete="off"
                        ref={(input: HTMLInputElement) => {
                          this.labelInput = input;
                        }}
                        className="AknTextField AknTextField--light"
                        id="pim_asset_manager.asset_family.create.input.label"
                        name="label"
                        value={this.props.data.getLabel(this.props.context.locale, false)}
                        onChange={this.onLabelUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                      <Flag
                        locale={createLocaleFromCode(this.props.context.locale)}
                        displayLanguage={false}
                        className="AknFieldContainer-inputSides"
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'labels')}
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
                    <div className="AknFieldContainer-inputContainer field-input">
                      <input
                        type="text"
                        autoComplete="off"
                        className="AknTextField AknTextField--light"
                        id="pim_asset_manager.asset_family.create.input.code"
                        name="code"
                        value={this.props.data.getCode().stringValue()}
                        onChange={this.onCodeUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'code')}
                  </div>
                  <button className="AknButton AknButton--apply ok" onClick={this.props.events.onSubmit}>
                    {__('pim_asset_manager.asset_family.create.confirm')}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          title="{__('pim_asset_manager.asset_family.create.cancel')}"
          className="AknFullPage-cancel cancel"
          onClick={this.props.events.onCancel}
          tabIndex={0}
        />
      </div>
    );
  }
}

export default connect(
  (state: IndexState): StateProps => {
    return {
      data: denormalizeAssetFamilyCreation(state.create.data),
      errors: state.create.errors,
      context: {
        locale: state.user.catalogLocale,
      },
    } as StateProps;
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onCodeUpdated: (value: string) => {
          dispatch(assetFamilyCreationCodeUpdated(value));
        },
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(assetFamilyCreationLabelUpdated(value, locale));
        },
        onCancel: () => {
          dispatch(assetFamilyCreationCancel());
        },
        onSubmit: () => {
          dispatch(createAssetFamily());
        },
      },
    } as DispatchProps;
  }
)(Create);
