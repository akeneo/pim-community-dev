import React, {FC, ReactElement, useState} from 'react';
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
  getColor,
  GroupsIcon,
  Link,
  ProductModelIcon,
  PublishIcon,
  Tile,
  Tiles,
  Tooltip,
} from 'akeneo-design-system';
import {IconProps} from 'akeneo-design-system/lib/icons/IconProps';
import {useTranslate} from '@akeneo-pim-community/shared';

type OperationCode = string;

type TilesWithReactProps = {
  operations: {code: OperationCode; label: string; icon: string}[];
  selectedOperationCode?: OperationCode;
  onChange: (code: OperationCode) => void;
};

const CustomTiles = styled(Tiles)`
  width: 730px;
`;

const InnerToolTip = styled.div`
  display: flex;
  flex-direction: column;
  gap: 4px;
  b,
  a {
    color: ${getColor('blue', 120)};
  }
`;

const ChooseApp: FC<TilesWithReactProps> = ({operations, selectedOperationCode, onChange}) => {
  const translate = useTranslate();
  const [currentOperationCode, setCurrentOperationCode] = useState<OperationCode | undefined>(selectedOperationCode);

  const handleChange = (code: OperationCode) => {
    onChange(code);
    setCurrentOperationCode(code);
  };

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
    };

    if (legacyIcon in iconsMap) {
      return iconsMap[legacyIcon];
    }

    console.warn('Icon not found: ', legacyIcon);
    return <ExplanationPointIcon />;
  };

  return (
    <CustomTiles size="small">
      {operations.map(operation => (
        <Tile
          key={operation.code}
          icon={getIcon(operation.icon)}
          onClick={() => handleChange(operation.code)}
          selected={currentOperationCode === operation.code}
          className="operation"
        >
          {operation.label}
          {operation.code === 'publish' && (
            <Tooltip width={352}>
              <InnerToolTip>
                <div>
                  <b>{translate('pimee_enrich.mass_edit.product.operation.publish.deprecation.title')}</b>
                </div>
                <div>{translate('pimee_enrich.mass_edit.product.operation.publish.deprecation.text')}</div>
                <div>
                  <Link
                    href={
                      'https://help.akeneo.com/en_US/serenity-take-the-power-over-your-products/important-update-deprecation-of-the-published-products-feature-from-akeneo-pim'
                    }
                    target="_blank"
                  >
                    {translate('pimee_enrich.mass_edit.product.operation.publish.deprecation.learn_more')}
                  </Link>
                </div>
              </InnerToolTip>
            </Tooltip>
          )}
        </Tile>
      ))}
    </CustomTiles>
  );
};

export {ChooseApp};
