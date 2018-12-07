import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import Tick from 'akeneoreferenceentity/application/component/app/icon/tick';

export enum RightLevel {
  None = 'none',
  View = 'view',
  Edit = 'edit',
  Own = 'own'
}
const ALL_GROUP = 'All';

type GroupName = string;
type EntityName = string;
type GroupRight = {
  group: Group,
  rightLevel: RightLevel
};

type Group = {
  name: GroupName
};
export type PermissionConfiguration = {[permissionCode: string]: GroupName[]}

type PermissionEditorProps = {
  group: Group;
  prioritizedRightLevels: RightLevel[]
  value: RightLevel;
  onChange: (groupCode: GroupName, newValue: RightLevel) => void
}

type PermissionCollectionEditorProps = {
  value: PermissionConfiguration,
  entityName: EntityName;
  groups: Group[];
  prioritizedRightLevels: RightLevel[]
  onChange: (newValue: PermissionConfiguration) => void
}

type PermissionCollectionEditorState = {
  rights: GroupRight[]
};

class PermissionEditor extends React.Component<PermissionEditorProps> {
  render() {
    const {group, prioritizedRightLevels, value, onChange} = this.props;
    const rightLevels = [RightLevel.None, ...prioritizedRightLevels];
    const groupRightLevelIndex = rightLevels.indexOf(value);

    return (
      <tr className="AknPermission-row">
        <td className="AknPermission-groupName AknGrid-bodyCell AknGrid-bodyCell--big">
          {group.name}
        </td>
        {rightLevels.map((rightLevel: RightLevel, currentRightLevelIndex: number) => {
          const isFirstColumn = currentRightLevelIndex === 0;
          const isLastColumn = currentRightLevelIndex === rightLevels.length - 1;
          const pillIsHigher = currentRightLevelIndex < groupRightLevelIndex;
          const pillIsLowerOrAtThisLevel = currentRightLevelIndex <= groupRightLevelIndex;

          return (
            <td
              className="AknPermission-level AknGrid-bodyCell"
              key={rightLevel}
            >
              <div className="AknPermission-rightLevel">
                <div className={
                  `AknPermission-barLeft ${
                    pillIsLowerOrAtThisLevel ? 'AknPermission-barLeft--active' : ''
                  } ${
                    isFirstColumn ? 'AknPermission-barLeft--transparent' : ''
                  }`
                } />
                <div
                  className={
                    `AknPermission-pill ${pillIsLowerOrAtThisLevel ? 'AknPermission-pill--active' : ''}`
                  }
                  onClick={() => {
                    onChange(group.name, rightLevel)
                  }}
                >
                  {!isFirstColumn && pillIsLowerOrAtThisLevel ? <Tick className="AknPermission-pillTick"/> : null}
                </div>
                <div className={`AknPermission-barRight ${
                  pillIsHigher ? 'AknPermission-barRight--active' : ''
                } ${
                  isLastColumn ? 'AknPermission-barRight--transparent' : ''
                }`} />
              </div>
            </td>
          )
        })}
      </tr>
    )
  }
}

export default class PermissionCollectionEditor extends React.Component<PermissionCollectionEditorProps, PermissionCollectionEditorState> {
  public state = {
    rights: []
  };

  private static getPrioritizedRigthLevels(permissions: PermissionConfiguration) {
    return Object.keys(permissions) as RightLevel[];
  }

  private onPermissionUpdated(groupCode: GroupName, newValue: RightLevel) {
    const newRights = this.state.rights.map((groupRight: GroupRight) => {
      if (groupRight.group.name === groupCode || ALL_GROUP === groupCode) {
        return {
          group: groupRight.group,
          rightLevel: newValue
        }
      }

      return groupRight;
    })
    this.setState({rights: newRights});

    const newPermissions = PermissionCollectionEditor.getPrioritizedRigthLevels(this.props.value).reduce(
      (newPermissions: PermissionConfiguration, rightLevel: RightLevel) => {
        return {
          ...newPermissions,
          [rightLevel]: PermissionCollectionEditor.getAllGroupCodesForTheGivenRightLevel(newRights, rightLevel, PermissionCollectionEditor.getPrioritizedRigthLevels(this.props.value))
        }
    }, {});

    this.props.onChange(newPermissions);
  }

  static getDerivedStateFromProps(props: PermissionCollectionEditorProps) {
    return {
      rights: PermissionCollectionEditor.rightCollectionToGroupCollection(props.value, props.groups)
    }
  }

  private static rightCollectionToGroupCollection(permissions: PermissionConfiguration, groups: Group[]) {
    return groups.map((group: Group) => ({
      group,
      rightLevel: this.getGroupRightLevel(permissions, group.name)
    }));
  }

  private static getAllGroupCodesForTheGivenRightLevel(rights: GroupRight[], rightLevel: RightLevel, prioritizedRightLevels: RightLevel[]) {
    const currentRightLevelIndex = prioritizedRightLevels.indexOf(rightLevel);

    return rights.filter((right: GroupRight) => prioritizedRightLevels.indexOf(right.rightLevel) >= currentRightLevelIndex).map((right: GroupRight) => right.group.name);
  }

  private static getGroupRightLevel(permissions: PermissionConfiguration, groupCode: GroupName) {
    // We iterate over all permission and check that the user group is present. If the group is not present, we set the default value to 'none'
    return PermissionCollectionEditor.getPrioritizedRigthLevels(permissions)
      .reduce((permission: RightLevel, currentPermissionCode: RightLevel) => {
        const isGrantedAtThisLevel = undefined !== permissions[currentPermissionCode] && permissions[currentPermissionCode].includes(groupCode);
        return isGrantedAtThisLevel ?
          currentPermissionCode :
          permission;
      }, RightLevel.None);
  }

  render() {
    const prioritizedRightLevels = PermissionCollectionEditor.getPrioritizedRigthLevels(this.props.value);

    return (
      <div className="AknGridContainer">
        <table className="AknPermission AknGrid">
        <thead className="AknPermission-header">
          <tr className="AknGrid-bodyRow">
            <th className="AknGrid-headerCell AknGrid-headerCell--center"></th>
            <th className="AknGrid-headerCell AknGrid-headerCell--center">none</th>
            {prioritizedRightLevels.map((rightLevel: RightLevel) => (
              <th key={rightLevel} className="AknGrid-headerCell AknGrid-headerCell--center">
                {rightLevel}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {this.state.rights.map(({group, rightLevel}: GroupRight) => (
            <PermissionEditor
              key={group.name}
              group={group}
              value={rightLevel}
              prioritizedRightLevels={prioritizedRightLevels}
              onChange={this.onPermissionUpdated.bind(this)}
            />
          ))}
        </tbody>
        </table>
      </div>
    )
  }
}
