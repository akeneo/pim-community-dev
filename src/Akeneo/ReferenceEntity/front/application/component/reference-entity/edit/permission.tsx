import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import {
  denormalizeReferenceEntity,
  NormalizedReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {breadcrumbConfiguration} from 'akeneoreferenceentity/application/component/reference-entity/edit';
import PermissionCollectionEditor from 'akeneoreferenceentity/tools/component/permission';
import {FormState} from 'akeneoreferenceentity/application/reducer/state';
import {permissionEditionUpdated} from 'akeneoreferenceentity/domain/event/reference-entity/permission';
import {
  RightLevel,
  denormalizePermissionCollection,
  PermissionCollection,
} from 'akeneoreferenceentity/domain/model/reference-entity/permission';
import {savePermission} from 'akeneoreferenceentity/application/action/reference-entity/permission';
import {canEditReferenceEntity} from 'akeneoreferenceentity/infrastructure/permission/edit';

const securityContext = require('pim/security-context');

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
          secondaryActions={() => {
            return null;
          }}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={this.props.permission.state.isDirty}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title AknSubsection-title--sticky AknSubsection-title--withHeader">
            <span className="group-label">{__('pim_reference_entity.reference_entity.permission.title')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--wide">
            <PermissionCollectionEditor
              readOnly={!this.props.rights.referenceEntity.edit}
              value={this.props.permission.data}
              prioritizedRightLevels={[RightLevel.View, RightLevel.Edit]}
              onChange={(newValue: PermissionCollection) => {
                this.props.events.onPermissionUpdated(newValue);
              }}
            />
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
            canEditReferenceEntity(),
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
