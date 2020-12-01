import React from 'react';
import Permission, {
  RightLevel,
  PermissionCollection,
  lowerLevel,
} from 'akeneoassetmanager/domain/model/asset-family/permission';
import __ from 'akeneoassetmanager/tools/translator';
import Key from 'akeneoassetmanager/tools/key';
import {CheckIcon, pimTheme} from 'akeneo-design-system';

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
                  tabIndex={0}
                  onClick={() => {
                    pillIsAtThisLevel && !isFirstColumn
                      ? onChange(groupCode, lowerLevel(rightLevel))
                      : onChange(groupCode, rightLevel);
                  }}
                  onKeyDown={(event: React.KeyboardEvent<HTMLDivElement>) => {
                    if (Key.Space === event.key) {
                      pillIsAtThisLevel && !isFirstColumn
                        ? onChange(groupCode, lowerLevel(rightLevel))
                        : onChange(groupCode, rightLevel);
                    }
                  }}
                >
                  {!isNoneLevel && pillIsLowerOrAtThisLevel ? (
                    <CheckIcon color={pimTheme.color.white} size={18} className="AknPermission-pillTick" />
                  ) : null}
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
  private tableHead: React.RefObject<HTMLTableSectionElement>;

  constructor(props: PermissionCollectionEditorProps) {
    super(props);

    this.tableHead = React.createRef<HTMLTableSectionElement>();
  }

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
    const topPosition = null !== this.tableHead.current ? this.tableHead.current.getBoundingClientRect().top - 20 : 0;

    return (
      <div className="AknGridContainer">
        <table className="AknPermission AknGrid">
          <thead className="AknPermission-header" ref={this.tableHead}>
            <tr className="AknGrid-bodyRow">
              <th
                className="AknGrid-headerCell AknGrid-headerCell--center AknGrid-headerCell--sticky"
                style={{top: `${topPosition}px`}}
              />
              {this.props.prioritizedRightLevels.map((rightLevel: RightLevel) => (
                <th
                  key={rightLevel}
                  className="AknGrid-headerCell AknGrid-headerCell--center AknGrid-headerCell--sticky"
                  style={{top: `${topPosition}px`}}
                >
                  <span
                    className="AknButton AknButton--small"
                    onClick={() => {
                      this.onAllPermissionUpdated(rightLevel);
                    }}
                    onKeyDown={(event: React.KeyboardEvent<HTMLSpanElement>) => {
                      if (Key.Space === event.key) {
                        this.onAllPermissionUpdated(rightLevel);
                      }
                    }}
                    tabIndex={0}
                    title={__('permission.mass_action', {rightLevel})}
                    data-right-level={rightLevel}
                  >
                    {rightLevel}
                  </span>
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
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
