import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import __ from 'akeneoassetmanager/tools/translator';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {
  assetCreationAssetCodeUpdated,
  assetCreationLabelUpdated,
  assetCreationCancel,
} from 'akeneoassetmanager/domain/event/asset/create';
import {createAsset} from 'akeneoassetmanager/application/action/asset/create';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import AssetFamily, {denormalizeAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import Key from 'akeneoassetmanager/tools/key';
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';

interface StateProps {
  context: {
    locale: string;
  };
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
  };
  errors: ValidationError[];
  assetFamily: AssetFamily;
}

interface DispatchProps {
  events: {
    onAssetCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onCancel: () => void;
    onSubmit: (createAnother: boolean) => void;
  };
}

interface CreateProps extends StateProps, DispatchProps {}

class Create extends React.Component<CreateProps, {createAnother: boolean}> {
  private labelInput: React.RefObject<HTMLInputElement>;
  state = {createAnother: false};
  public props: CreateProps;

  constructor(props: CreateProps) {
    super(props);

    this.labelInput = React.createRef();
  }

  componentDidMount() {
    if (null !== this.labelInput.current) {
      this.labelInput.current.focus();
    }
  }

  private onAssetCodeUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onAssetCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.onSubmit(this.state.createAnother);
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
        <div className="modal-body  creation">
          <div className="AknFullPage">
            <div className="AknFullPage-content AknFullPage-content--withIllustration">
              <div>
                <img src="bundles/pimui/images/illustrations/Assets.svg" className="AknFullPage-image" />
              </div>
              <div>
                <div className="AknFormContainer">
                  <div className="AknFullPage-titleContainer">
                    <div className="AknFullPage-subTitle">{__('pim_asset_manager.asset.create.subtitle')}</div>
                    <div className="AknFullPage-title">
                      {__('pim_asset_manager.asset.create.title', {
                        entityLabel: this.props.assetFamily.getLabel(this.props.context.locale).toLowerCase(),
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
                        ref={this.labelInput}
                        autoComplete="off"
                        type="text"
                        className="AknTextField AknTextField--light"
                        id="pim_asset_manager.asset.create.input.label"
                        name="label"
                        value={
                          undefined === this.props.data.labels[this.props.context.locale]
                            ? ''
                            : this.props.data.labels[this.props.context.locale]
                        }
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
                        value={this.props.data.code}
                        onChange={this.onAssetCodeUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'code')}
                  </div>
                  <div className="AknFieldContainer" data-code="create_another">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_asset_manager.asset.create.input.create_another"
                      >
                        <Checkbox
                          id="pim_asset_manager.asset.create.input.create_another"
                          value={this.state.createAnother}
                          onChange={(newValue: boolean) => this.setState({createAnother: newValue})}
                        />
                        <span
                          onClick={() => {
                            this.setState({createAnother: !this.state.createAnother});
                          }}
                        >
                          {__('pim_asset_manager.asset.create.input.create_another')}
                        </span>
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer" />
                  </div>
                  <button
                    className="AknButton AknButton--apply ok"
                    onClick={() => {
                      this.props.events.onSubmit(this.state.createAnother);
                    }}
                  >
                    {__('pim_asset_manager.asset.create.confirm')}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          title="{__('pim_asset_manager.asset.create.cancel')}"
          className="AknFullPage-cancel cancel"
          onClick={this.props.events.onCancel}
        />
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    return {
      data: state.createAsset.data,
      errors: state.createAsset.errors,
      context: {
        locale: state.user.catalogLocale,
      },
      assetFamily: denormalizeAssetFamily(state.form.data),
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAssetCodeUpdated: (value: string) => {
          dispatch(assetCreationAssetCodeUpdated(value));
        },
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(assetCreationLabelUpdated(value, locale));
        },
        onCancel: () => {
          dispatch(assetCreationCancel());
        },
        onSubmit: (createAnother: boolean) => {
          dispatch(createAsset(createAnother));
        },
      },
    };
  }
)(Create);
