import React, {useRef} from 'react';
import Permission, {
  RightLevel,
  PermissionCollection,
  lowerLevel,
} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {CheckIcon, pimTheme, Key, Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type GroupName = string;

type PermissionEditorProps = {
  groupCode: GroupName;
  prioritizedRightLevels: RightLevel[];
  value: RightLevel;
  readOnly: boolean;
  onChange: (groupCode: GroupName, newValue: RightLevel) => void;
};

const PermissionEditor = ({groupCode, prioritizedRightLevels, value, readOnly, onChange}: PermissionEditorProps) => {
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
                className={`AknPermission-barLeft ${pillIsLowerOrAtThisLevel ? 'AknPermission-barLeft--active' : ''} ${
                  readOnly ? 'AknPermission-barLeft--disabled' : ''
                } ${isFirstColumn ? 'AknPermission-barLeft--transparent' : ''}`}
              />
              <div
                className={`AknPermission-pill ${pillIsLowerOrAtThisLevel ? 'AknPermission-pill--active' : ''} ${
                  readOnly ? 'AknPermission-pill--disabled' : ''
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
                  <CheckIcon color={pimTheme.color.white} size={16} className="AknPermission-pillTick" />
                ) : null}
              </div>
              <div
                className={`AknPermission-barRight ${pillIsHigher ? 'AknPermission-barRight--active' : ''} ${
                  readOnly ? 'AknPermission-barRight--disabled' : ''
                } ${isLastColumn ? 'AknPermission-barRight--transparent' : ''}`}
              />
            </div>
          </td>
        );
      })}
    </tr>
  );
};

type PermissionCollectionEditorProps = {
  value: PermissionCollection;
  readOnly: boolean;
  prioritizedRightLevels: RightLevel[];
  onChange: (newValue: PermissionCollection) => void;
};

const PermissionCollectionEditor = ({
  readOnly,
  value,
  prioritizedRightLevels,
  onChange,
}: PermissionCollectionEditorProps) => {
  const translate = useTranslate();
  const tableHeadRef = useRef<HTMLTableSectionElement>(null);
  const topPosition = tableHeadRef.current ? tableHeadRef.current.getBoundingClientRect().top - 20 : 0;
  const onPermissionUpdated = (groupCode: GroupName, newValue: RightLevel) => {
    if (!readOnly) {
      onChange(value.setPermission(groupCode, newValue));
    }
  };

  const onAllPermissionUpdated = (newValue: RightLevel) => {
    if (!readOnly) {
      onChange(value.setAllPermissions(newValue));
    }
  };

  return (
    <div className="AknGridContainer">
      <table className="AknPermission AknGrid">
        <thead className="AknPermission-header" ref={tableHeadRef}>
          <tr className="AknGrid-bodyRow">
            <th
              className="AknGrid-headerCell AknGrid-headerCell--center AknGrid-headerCell--sticky"
              style={{top: `${topPosition}px`}}
            />
            {prioritizedRightLevels.map((rightLevel: RightLevel) => (
              <th
                key={rightLevel}
                className="AknGrid-headerCell AknGrid-headerCell--center AknGrid-headerCell--sticky"
                style={{top: `${topPosition}px`}}
              >
                <Button
                  level="tertiary"
                  ghost={true}
                  onClick={() => onAllPermissionUpdated(rightLevel)}
                  title={translate('permission.mass_action', {rightLevel})}
                  data-right-level={rightLevel}
                >
                  {rightLevel}
                </Button>
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {value.map((permission: Permission) => (
            <PermissionEditor
              key={permission.getUserGroupIdentifier()}
              readOnly={readOnly}
              groupCode={permission.getUserGroupName()}
              value={permission.getRightLevel()}
              prioritizedRightLevels={prioritizedRightLevels}
              onChange={onPermissionUpdated}
            />
          ))}
        </tbody>
      </table>
    </div>
  );
};

export {PermissionCollectionEditor};
