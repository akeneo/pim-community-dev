import React, {ReactElement, FC, useState} from 'react';
import styled from 'styled-components';
import {
  AddAttributeIcon,
  AssociateIcon,
  AttributeBooleanIcon,
  EditIcon,
  EntityIcon,
  ExplanationPointIcon,
  FolderInIcon,
  FolderMovedIcon,
  FolderOutIcon,
  GroupsIcon,
  ProductModelIcon,
  PublishIcon,
  Tile,
  Tiles,
} from 'akeneo-design-system';
import {
  IconProps
} from "akeneo-design-system/lib/icons/IconProps";

type OperationCode = string;

type TilesWithReactProps = {
  operations: {code: OperationCode, label: string, icon: string}[];
  selectedOperationCode?: OperationCode;
  onChange: (code: OperationCode) => void;
}

const CustomTiles = styled(Tiles)`
  width: 730px;
`;

const ChooseApp: FC<TilesWithReactProps> = ({operations, selectedOperationCode, onChange}) => {
  const [currentOperationCode, setCurrentOperationCode] = useState<OperationCode | undefined>(selectedOperationCode);

  const handleChange = (code: OperationCode) => {
    onChange(code);
    setCurrentOperationCode(code);
  }

  const getIcon = (legacyIcon: string): ReactElement<IconProps> => {
    const iconsMap: {[legacyIcon: string]: ReactElement<IconProps>} = {
      'icon-edit': <EditIcon />,
      'icon-add-attribute-values': <AddAttributeIcon />,
      'icon-template': <EntityIcon />,
      'icon-enable': <AttributeBooleanIcon />,
      'icon-folder_in': <FolderInIcon />,
      'icon-folder_move': <FolderMovedIcon />,
      'icon-folder_out': <FolderOutIcon />,
      'icon-model': <ProductModelIcon />,
      'icon-association': <AssociateIcon />,
      'icon-publish': <PublishIcon />,
      'icon-groups': <GroupsIcon />,
    }

    if (legacyIcon in iconsMap) {
      return iconsMap[legacyIcon];
    }

    console.warn('Icon not found: ', legacyIcon);
    return <ExplanationPointIcon />;
  }

  return <CustomTiles size="small">
    {operations.map((operation) => (
      <Tile
        key={operation.code}
        icon={getIcon(operation.icon)}
        onClick={() => handleChange(operation.code)}
        selected={currentOperationCode === operation.code}
      >
        {operation.label}
      </Tile>
    ))}
  </CustomTiles>
}

export {ChooseApp};
