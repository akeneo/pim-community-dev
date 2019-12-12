import * as React from 'react';
import {connect} from 'react-redux';
import { JsonEditor as Editor } from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import __ from 'akeneoassetmanager/tools/translator';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import TransformationCollection from 'akeneoassetmanager/domain/model/asset-family/transformation/transformation-collection';
import {
  assetFamilyTransformationsUpdated,
  saveAssetFamily,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import Ajv from 'ajv';
const ajv = new Ajv({ allErrors: true, verbose: true });

interface StateProps {
  assetFamily: AssetFamily;
}

type AssetFamilyTransformationEditorProps = {
  transformations: TransformationCollection;
  onAssetFamilyTransformationsChange: (transformations: TransformationCollection) => void;
};

const AssetFamilyTransformationEditor = ({
  transformations,
  onAssetFamilyTransformationsChange,
}: AssetFamilyTransformationEditorProps) => {
  let transformationSchema = {
    "title": "BackendAssetFamilyTransformation",
    "type": "array",
    "items": {
      "type": "object",
      "properties": {
        "source": {
          "type": "object",
          "properties": {
            "attribute": {
              "type": "string",
            },
            "channel": {
              "type": [
                "string",
                "null"
              ]
            },
            "locale": {
              "type": [
                "string",
                "null"
              ]
            }
          },
          "required": [
            "attribute",
            "channel",
            "locale"
          ],
          "additionalProperties": false
        },
        "target": {
          "type": "object",
          "properties": {
            "attribute": {
              "type": "string"
            },
            "channel": {
              "type": [
                "string",
                "null"
              ]
            },
            "locale": {
              "type": [
                "string",
                "null"
              ]
            }
          },
          "required": [
            "attribute",
            "channel",
            "locale"
          ],
          "additionalProperties": false
        },
        "operations": {
          "type": "array",
          "items": {
            "type": "object",
            "required": [
              "type"
            ],
            "additionalProperties": true
          }
        },
        "filename_prefix": {
          "type": "string"
        },
        "filename_suffix": {
          "type": "string"
        }
      },
      "required": [
        "source",
        "target",
        "operations",
      ],
      "additionalProperties": false
    }
  };

  //https://github.com/vankop/jsoneditor-react/blob/HEAD/src/Editor.jsx
  return (
    <Editor
      value={JSON.parse(transformations)}
      onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
        onAssetFamilyTransformationsChange(event.target.value);
      }}
      mode='code'
      schema={transformationSchema}
      ajv={ajv}
    />
  )
};

interface DispatchProps {
  events: {
    onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => void;
    onSaveEditForm: () => void;
  };
}

class Transformation extends React.Component<StateProps & DispatchProps, Transformation> {
  props: StateProps & DispatchProps;

  render() {
    const assetFamily = this.props.assetFamily;

    return (
      <React.Fragment>
        <Header
          label={__('pim_asset_manager.asset_family.tab.transformations')}
          image={assetFamily.image}
          primaryAction={(defaultFocus: React.RefObject<any>) => (
            <button
              className="AknButton AknButton--apply"
              onClick={this.props.events.onSaveEditForm}
              ref={defaultFocus}
            >
              {__('pim_asset_manager.asset_family.button.save')}
            </button>
          )}
          secondaryActions={() => {
            return null;
          }}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={false}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <AssetFamilyTransformationEditor
          transformations={this.props.assetFamily.transformations}
          onAssetFamilyTransformationsChange={(transformations: TransformationCollection) => {
            this.props.events.onAssetFamilyTransformationsUpdated(transformations);
          }}
        />
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    return {
      assetFamily: state.form.data,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => {
          dispatch(assetFamilyTransformationsUpdated(transformations));
        },
        onSaveEditForm: () => {
          dispatch(saveAssetFamily());
        },
      },
    };
  }
)(Transformation);
