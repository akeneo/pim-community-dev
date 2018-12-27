import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import {
  denormalizeReferenceEntity, NormalizedReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {breadcrumbConfiguration} from 'akeneoreferenceentity/application/component/reference-entity/edit';
import PermissionCollectionEditor, {PermissionConfiguration, Group as UserGroup, RightLevel} from 'akeneoreferenceentity/tools/component/permission';
import {FormState} from 'akeneoreferenceentity/application/reducer/state';
import {permissionEditionUpdated} from 'akeneoreferenceentity/domain/event/reference-entity/permission';

const fetcherRegistry = require('pim/fetcher-registry');

interface StateProps {
  referenceEntity: NormalizedReferenceEntity;
  permission: {
    data: PermissionConfiguration,
    state: FormState;
  },
  context: {
    locale: string;
  },
  acls: {
    edit: boolean
  }
}

interface DispatchProps {
  events: {
    onPermissionUpdated: (updatedConfiguration: PermissionConfiguration) => void;
    onSavePermissionEditForm: () => void;
  };
}

class Properties extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;
  state: {
    userGroups: UserGroup[]
  } = {
    userGroups: []
  };

  componentDidMount() {
    fetcherRegistry.getFetcher('user-group')
      .fetchAll().then((userGroups: UserGroup[]) => {
        this.setState({userGroups});
      })
  }

  render() {
    const referenceEntity = denormalizeReferenceEntity(this.props.referenceEntity);

    return (
      <React.Fragment>
        <Header
          label={referenceEntity.getLabel(this.props.context.locale)}
          image={referenceEntity.getImage()}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return this.props.acls.edit ? (
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
          <header className="AknSubsection-title">
            <span className="group-label">{__('pim_reference_entity.reference_entity.permission.title')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--withPadding">
            <PermissionCollectionEditor
              groups={this.state.userGroups}
              entityName={'reference_entity'}//To Change
              value={this.props.permission.data}
              prioritizedRightLevels={Object.keys(this.props.permission.data) as RightLevel[]}
              onChange={(newValue: PermissionConfiguration) => {
                  this.props.events.onPermissionUpdated(newValue)
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
      permission: state.permission,
      context: {
        locale,
      },
      acls: {
        edit: true
      }
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onPermissionUpdated: (permission: PermissionConfiguration) => {
          dispatch(permissionEditionUpdated(permission));
        },
        onSavePermissionEditForm: () => {
          // dispatch(permissionEditionUpdated(permission));
        },
      },
    };
  }
)(Properties);
