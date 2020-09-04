import { GroupCode } from '../../../../../models';
import { GroupSelector } from '../../../../../components/Selectors/GroupSelector';
import React from 'react';

type Props = {
  groupCodes: GroupCode[];
  currentCatalogLocale: string;
  onChange: (groupCodes: GroupCode[]) => void;
};

const AssociationsGroupsSelector: React.FC<Props> = ({
  groupCodes,
  currentCatalogLocale,
  onChange,
}) => {
  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const handleSelectGroup = (groupCode: GroupCode, index?: number) => {
    if (!groupCode && typeof index !== 'undefined') {
      groupCodes.splice(index, 1);
    } else if (!groupCodes.includes(groupCode)) {
      if (typeof index !== 'undefined') {
        groupCodes[index] = groupCode;
      } else {
        groupCodes.push(groupCode);
      }
    }
    onChange(groupCodes);
  };

  return (
    <ul>
      {groupCodes.map((groupCode, i) => {
        return (
          <li key={groupCode} className={'AknBadgedSelector-item'}>
            <GroupSelector
              currentCatalogLocale={currentCatalogLocale}
              value={groupCode}
              id={`group-selector-${groupCode}`}
              allowClear={true}
              hiddenLabel
              onChange={groupCode => handleSelectGroup(groupCode, i)}
              placeholder={' '} // A placeholder is needed for allowClear
            />
          </li>
        );
      })}
      <li className={'AknBadgedSelector-item'}>
        <GroupSelector
          currentCatalogLocale={currentCatalogLocale}
          value={''}
          id={'group-selector-new'}
          allowClear={false}
          hiddenLabel
          placeholder={'TODO Add group'}
          onSelecting={(event: any) => {
            event.preventDefault();
            setCloseTick(!closeTick);
            handleSelectGroup(event.val);
          }}
          closeTick={closeTick}
        />
      </li>
    </ul>
  );
};

export { AssociationsGroupsSelector };
