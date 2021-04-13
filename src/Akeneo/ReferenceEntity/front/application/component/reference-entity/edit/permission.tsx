import React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import {
  denormalizeReferenceEntity,
  NormalizedReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {RefEntityBreadcrumb} from 'akeneoreferenceentity/application/component/app/breadcrumb';
import PermissionCollectionEditor from 'akeneoreferenceentity/tools/component/permission';
import {FormState} from 'akeneoreferenceentity/application/reducer/state';
import {permissionEditionUpdated} from 'akeneoreferenceentity/domain/event/reference-entity/permission';
import {
  RightLevel,
  denormalizePermissionCollection,
  PermissionCollection,
} from 'akeneoreferenceentity/domain/model/reference-entity/permission';
import {savePermission} from 'akeneoreferenceentity/application/action/reference-entity/permission';
import {canEditReferenceEntity} from 'akeneoreferenceentity/application/reducer/right';

const securityContext = require('pim/security-context');
const routing = require('routing');

interface StateProps {
  referenceEntity: NormalizedReferenceEntity;
  permission: {
    data: PermissionCollection;
    state: FormState;
  };
  context: {
    locale: string;
  };
  rights: {
    referenceEntity: {
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
    const referenceEntity = denormalizeReferenceEntity(this.props.referenceEntity);

    return (
      <React.Fragment>
        <Header
          label={referenceEntity.getLabel(this.props.context.locale)}
          image={referenceEntity.getImage()}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return this.props.rights.referenceEntity.edit && !this.props.permission.data.isEmpty() ? (
              <button
                className="AknButton AknButton--apply"
                onClick={this.props.events.onSavePermissionEditForm}
                ref={defaultFocus}
              >
                {__('pim_reference_entity.reference_entity.button.save_permission')}
              </button>
            ) : null;
          }}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={this.props.permission.state.isDirty}
          breadcrumb={<RefEntityBreadcrumb referenceEntityIdentifier={referenceEntity.getIdentifier().stringValue()} />}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title AknSubsection-title--sticky AknSubsection-title--withHeader">
            <span className="group-label">{__('pim_reference_entity.reference_entity.permission.title')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--wide">
            {!this.props.permission.data.isEmpty() ? (
              <PermissionCollectionEditor
                readOnly={!this.props.rights.referenceEntity.edit}
                value={this.props.permission.data}
                prioritizedRightLevels={[RightLevel.View, RightLevel.Edit]}
                onChange={(newValue: PermissionCollection) => {
                  this.props.events.onPermissionUpdated(newValue);
                }}
              />
            ) : (
              <div className="AknGridContainer-noData">
                <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--user-group" />
                <div className="AknGridContainer-noDataTitle">
                  {__('pim_reference_entity.permission.no_data.title')}
                </div>
                <div className="AknGridContainer-noDataSubtitle">
                  {__('pim_reference_entity.permission.no_data.subtitle')}{' '}
                  {this.props.rights.userGroup.create ? (
                    <a href={`#${routing.generate('pim_user_group_index')}`} target="_blank">
                      {__('pim_reference_entity.permission.no_data.link')}
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
      referenceEntity: state.form.data,
      permission: {
        data: denormalizePermissionCollection(state.permission.data),
        state: state.permission.state,
      },
      context: {
        locale,
      },
      rights: {
        referenceEntity: {
          edit:
            securityContext.isGranted('akeneo_referenceentity_reference_entity_manage_permission') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.identifier),
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
