import * as React from 'react';
import Tick from 'akeneoreferenceentity/application/component/app/icon/tick';
import Permission, {
  RightLevel,
  PermissionCollection,
  lowerLevel,
} from 'akeneoreferenceentity/domain/model/reference-entity/permission';

type GroupName = string;

type PermissionEditorProps = {
  groupCode: GroupName;
  prioritizedRightLevels: RightLevel[];
  value: RightLevel;
  readOnly: boolean;
  onChange: (groupCode: GroupName, newValue: RightLevel) => void;
};

class PermissionEditor extends React.Component<PermissionEditorProps> {
  render() {
    const {groupCode, prioritizedRightLevels, value, onChange} = this.props;
    const groupRightLevelIndex = prioritizedRightLevels.indexOf(value);

    return (
      <tr className="AknPermission-row" data-user-group-code={groupCode}>
        <td className="AknPermission-groupName AknGrid-bodyCell AknGrid-bodyCell--big">{groupCode}</td>
        {prioritizedRightLevels.map((rightLevel: RightLevel, currentRightLevelIndex: number) => {
          const isNoneLevel = rightLevel === RightLevel.None;
          const isFirstColumn = currentRightLevelIndex === 0;
          const isLastColumn = currentRightLevelIndex === prioritizedRightLevels.length - 1;
          const pillIsHigher = currentRightLevelIndex < groupRightLevelIndex;
          const pillIsLowerOrAtThisLevel = currentRightLevelIndex <= groupRightLevelIndex;
          const pillIsAtThisLevel = currentRightLevelIndex === groupRightLevelIndex;

          return (
            <td className="AknPermission-level AknGrid-bodyCell" key={rightLevel} data-right-level={rightLevel}>
              <div className="AknPermission-rightLevel">
                <div
                  className={`AknPermission-barLeft ${
                    pillIsLowerOrAtThisLevel ? 'AknPermission-barLeft--active' : ''
                  } ${this.props.readOnly ? 'AknPermission-barLeft--disabled' : ''} ${
                    isFirstColumn ? 'AknPermission-barLeft--transparent' : ''
                  }`}
                />
                <div
                  className={`AknPermission-pill ${pillIsLowerOrAtThisLevel ? 'AknPermission-pill--active' : ''} ${
                    this.props.readOnly ? 'AknPermission-pill--disabled' : ''
                  }`}
                  onClick={() => {
                    pillIsAtThisLevel && !isFirstColumn
                      ? onChange(groupCode, lowerLevel(rightLevel))
                      : onChange(groupCode, rightLevel);
                  }}
                >
                  {!isNoneLevel && pillIsLowerOrAtThisLevel ? <Tick className="AknPermission-pillTick" /> : null}
                </div>
                <div
                  className={`AknPermission-barRight ${pillIsHigher ? 'AknPermission-barRight--active' : ''} ${
                    this.props.readOnly ? 'AknPermission-barRight--disabled' : ''
                  } ${isLastColumn ? 'AknPermission-barRight--transparent' : ''}`}
                />
              </div>
            </td>
          );
        })}
      </tr>
    );
  }
}

type PermissionCollectionEditorProps = {
  value: PermissionCollection;
  readOnly: boolean;
  prioritizedRightLevels: RightLevel[];
  onChange: (newValue: PermissionCollection) => void;
};

export default class PermissionCollectionEditor extends React.Component<PermissionCollectionEditorProps> {
  private onPermissionUpdated(groupCode: GroupName, newValue: RightLevel) {
    if (!this.props.readOnly) {
      this.props.onChange(this.props.value.setPermission(groupCode, newValue));
    }
  }

  private onAllPermissionUpdated(newValue: RightLevel) {
    if (!this.props.readOnly) {
      this.props.onChange(this.props.value.setAllPermissions(newValue));
    }
  }

  render() {
    return (
      <div className="AknGridContainer">
        <table className="AknPermission AknGrid">
          <thead className="AknPermission-header">
            <tr className="AknGrid-bodyRow">
              <th className="AknGrid-headerCell AknGrid-headerCell--center" />
              {this.props.prioritizedRightLevels.map((rightLevel: RightLevel) => (
                <th key={rightLevel} className="AknGrid-headerCell AknGrid-headerCell--center">
                  {rightLevel}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            <tr className="AknPermission-row AknPermission-row--massAction">
              <td className="AknPermission-level AknGrid-bodyCell" />
              {this.props.prioritizedRightLevels.map((rightLevel: RightLevel) => (
                <td
                  key={rightLevel}
                  className="AknPermission-level AknGrid-bodyCell"
                  onClick={() => {
                    this.onAllPermissionUpdated(rightLevel);
                  }}
                  data-right-level={rightLevel}
                >
                  <div className="AknPermission-rightLevel">
                    <div className="AknPermission-barLeft AknPermission-barLeft--transparent" />
                    <div className="AknPermission-pill" />
                    <div className="AknPermission-barRight AknPermission-barRight--transparent" />
                  </div>
                </td>
              ))}
            </tr>
            {this.props.value.map((permission: Permission) => (
              <PermissionEditor
                key={permission.getUserGroupIdentifier()}
                readOnly={this.props.readOnly}
                groupCode={permission.getUserGroupName()}
                value={permission.getRightLevel()}
                prioritizedRightLevels={this.props.prioritizedRightLevels}
                onChange={this.onPermissionUpdated.bind(this)}
              />
            ))}
          </tbody>
        </table>
      </div>
    );
  }
}
