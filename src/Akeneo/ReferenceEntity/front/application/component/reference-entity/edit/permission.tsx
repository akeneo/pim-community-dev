import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import {
  denormalizeReferenceEntity, NormalizedReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {breadcrumbConfiguration} from 'akeneoreferenceentity/application/component/reference-entity/edit';
import PermissionCollectionEditor from 'akeneoreferenceentity/tools/component/permission';
import {FormState} from 'akeneoreferenceentity/application/reducer/state';
import {permissionEditionUpdated} from 'akeneoreferenceentity/domain/event/reference-entity/permission';
import {RightLevel, denormalizePermissionCollection, PermissionCollection} from 'akeneoreferenceentity/domain/model/reference-entity/permission';

const fetcherRegistry = require('pim/fetcher-registry');

type UserGroup = {
  name: string;
}

interface StateProps {
  referenceEntity: NormalizedReferenceEntity;
  permission: {
    data: PermissionCollection,
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
    onPermissionUpdated: (updatedConfiguration: PermissionCollection) => void;
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
          <div className="AknFormContainer AknFormContainer--wide">
            <PermissionCollectionEditor
              value={this.props.permission.data}
              prioritizedRightLevels={[RightLevel.View, RightLevel.Edit]}
              onChange={(newValue: PermissionCollection) => {
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
      permission: {
        data: denormalizePermissionCollection(state.permission.data),
        state: state.permission.state
      },
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
        onPermissionUpdated: (permission: PermissionCollection) => {
          dispatch(permissionEditionUpdated(permission));
        },
        onSavePermissionEditForm: () => {
          // dispatch(permissionEditionUpdated(permission));
        },
      },
    };
  }
)(Properties);
