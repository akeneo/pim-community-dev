import * as React from 'react';
import styled from 'styled-components';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import __ from 'akeneoassetmanager/tools/translator';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import PermissionCollectionEditor from 'akeneoassetmanager/tools/component/permission';
import {FormState} from 'akeneoassetmanager/application/reducer/state';
import {permissionEditionUpdated} from 'akeneoassetmanager/domain/event/asset-family/permission';
import {
  denormalizePermissionCollection,
  PermissionCollection,
  RightLevel,
} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {savePermission} from 'akeneoassetmanager/application/action/asset-family/permission';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';

const securityContext = require('pim/security-context');
const routing = require('routing');

export const StickyHeader = styled.header.attrs(() => ({className: 'AknSubsection-title AknSubsection-title--sticky'}))`
  top: 160px;
  padding: 0;
`;

interface StateProps {
  assetFamily: AssetFamily;
  permission: {
    data: PermissionCollection;
    state: FormState;
  };
  context: {
    locale: string;
  };
  rights: {
    assetFamily: {
      edit: boolean;
    };
    userGroup: {
      create: boolean;
    };
  };
}

interface DispatchProps {
  events: {
    onPermissionUpdated: (updatedConfiguration: PermissionCollection) => void;
    onSavePermissionEditForm: () => void;
  };
}

class Permission extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    const assetFamily = this.props.assetFamily;

    return (
      <React.Fragment>
        <Header
          label={getAssetFamilyLabel(assetFamily, this.props.context.locale)}
          image={assetFamily.image}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return this.props.rights.assetFamily.edit && !this.props.permission.data.isEmpty() ? (
              <button
                className="AknButton AknButton--apply"
                onClick={this.props.events.onSavePermissionEditForm}
                ref={defaultFocus}
              >
                {__('pim_asset_manager.asset_family.button.save_permission')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return null;
          }}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={this.props.permission.state.isDirty}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <div className="AknSubsection">
          <StickyHeader>
            <span className="group-label">{__('pim_asset_manager.asset_family.permission.title')}</span>
          </StickyHeader>
          <div className="AknFormContainer AknFormContainer--wide">
            {!this.props.permission.data.isEmpty() ? (
              <PermissionCollectionEditor
                readOnly={!this.props.rights.assetFamily.edit}
                value={this.props.permission.data}
                prioritizedRightLevels={[RightLevel.View, RightLevel.Edit]}
                onChange={(newValue: PermissionCollection) => {
                  this.props.events.onPermissionUpdated(newValue);
                }}
              />
            ) : (
              <div className="AknGridContainer-noData">
                <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--user-group" />
                <div className="AknGridContainer-noDataTitle">{__('pim_asset_manager.permission.no_data.title')}</div>
                <div className="AknGridContainer-noDataSubtitle">
                  {__('pim_asset_manager.permission.no_data.subtitle')}{' '}
                  {this.props.rights.userGroup.create ? (
                    <a href={`#${routing.generate('pim_user_group_index')}`} target="_blank">
                      {__('pim_asset_manager.permission.no_data.link')}
                    </a>
                  ) : null}
                </div>
              </div>
            )}
          </div>
        </div>
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      assetFamily: state.form.data,
      permission: {
        data: denormalizePermissionCollection(state.permission.data),
        state: state.permission.state,
      },
      context: {
        locale,
      },
      rights: {
        assetFamily: {
          edit:
            securityContext.isGranted('akeneo_assetmanager_asset_family_manage_permission') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
        userGroup: {
          create:
            securityContext.isGranted('pim_user_group_index') && securityContext.isGranted('pim_user_group_create'),
        },
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onPermissionUpdated: (permission: PermissionCollection) => {
          dispatch(permissionEditionUpdated(permission));
        },
        onSavePermissionEditForm: () => {
          dispatch(savePermission());
        },
      },
    };
  }
)(Permission);
