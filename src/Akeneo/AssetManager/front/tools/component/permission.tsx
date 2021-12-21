import React from 'react';
import styled from 'styled-components';
import {Button, Table, ProgressIndicator} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {RightLevel, PermissionCollection, lowerLevel} from 'akeneoassetmanager/domain/model/asset-family/permission';

const RightButtons = styled.div`
  display: flex;
  justify-content: space-between;
  padding: 0 85px;
`;

const RightButtonsHeaderCell = styled(Table.HeaderCell)`
  width: 450px;
`;

const FullWidthProgressIndicator = styled(ProgressIndicator)`
  width: 100%;
`;

type PermissionEditorProps = {
  groupCode: string;
  prioritizedRightLevels: RightLevel[];
  value: RightLevel;
  readOnly: boolean;
  onChange: (groupCode: string, newValue: RightLevel) => void;
};

const PermissionEditor = ({groupCode, prioritizedRightLevels, value, readOnly, onChange}: PermissionEditorProps) => {
  const groupRightLevelIndex = prioritizedRightLevels.indexOf(value);

  return (
    <Table.Row>
      <Table.Cell>{groupCode}</Table.Cell>
      <Table.Cell>
        <FullWidthProgressIndicator>
          {prioritizedRightLevels.map((rightLevel, currentRightLevelIndex) => {
            const isFirstColumn = currentRightLevelIndex === 0;
            const pillIsLowerOrAtThisLevel = currentRightLevelIndex <= groupRightLevelIndex;
            const pillIsAtThisLevel = currentRightLevelIndex === groupRightLevelIndex;
            const handleStepClick = () =>
              pillIsAtThisLevel && !isFirstColumn
                ? onChange(groupCode, lowerLevel(rightLevel))
                : onChange(groupCode, rightLevel);

            return (
              <ProgressIndicator.Step
                key={rightLevel}
                disabled={readOnly}
                onClick={readOnly ? undefined : handleStepClick}
                state={pillIsLowerOrAtThisLevel ? 'done' : 'todo'}
              />
            );
          })}
        </FullWidthProgressIndicator>
      </Table.Cell>
    </Table.Row>
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
  const onPermissionUpdated = (groupCode: string, newValue: RightLevel) => {
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
    <Table>
      <Table.Header sticky={44}>
        <Table.HeaderCell />
        <RightButtonsHeaderCell>
          <RightButtons>
            {prioritizedRightLevels.map(rightLevel => (
              <Button
                key={rightLevel}
                disabled={readOnly}
                level="tertiary"
                ghost={true}
                onClick={() => onAllPermissionUpdated(rightLevel)}
                title={translate('permission.mass_action', {rightLevel})}
              >
                {rightLevel}
              </Button>
            ))}
          </RightButtons>
        </RightButtonsHeaderCell>
      </Table.Header>
      <Table.Body>
        {value.map(permission => (
          <PermissionEditor
            key={permission.getUserGroupIdentifier()}
            readOnly={readOnly}
            groupCode={permission.getUserGroupName()}
            value={permission.getRightLevel()}
            prioritizedRightLevels={prioritizedRightLevels}
            onChange={onPermissionUpdated}
          />
        ))}
      </Table.Body>
    </Table>
  );
};

export {PermissionCollectionEditor};
