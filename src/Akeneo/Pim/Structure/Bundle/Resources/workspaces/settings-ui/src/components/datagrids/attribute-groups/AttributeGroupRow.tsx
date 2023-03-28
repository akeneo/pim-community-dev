import React, {useCallback, MouseEvent, memo} from 'react';
import {Table, Badge} from 'akeneo-design-system';
import {
  useTranslate,
  useFeatureFlags,
  useUserContext,
  getLabel,
  useRoute,
  useRouter,
  useSecurity,
} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '../../../models';

type AttributeGroupRowProps = {
  attributeGroup: AttributeGroup;
  isSelected: boolean;
  onSelectionChange: (attributeGroup: AttributeGroup, selected: boolean) => void;
};

const AttributeGroupRow = memo(({attributeGroup, isSelected, onSelectionChange, ...rest}: AttributeGroupRowProps) => {
  const catalogLocale = useUserContext().get('catalogLocale');
  const translate = useTranslate();
  const router = useRouter();
  const {isEnabled} = useFeatureFlags();
  const {isGranted} = useSecurity();
  const editRoute = useRoute('pim_enrich_attributegroup_edit', {identifier: attributeGroup.code});

  const handleRowClick = useCallback(
    (event: MouseEvent<HTMLTableRowElement>) => {
      if (event.metaKey || event.ctrlKey) {
        const newTab = window.open(`#${editRoute}`, '_blank');
        newTab?.focus();

        return;
      }

      router.redirect(editRoute);
    },
    [router, editRoute]
  );

  const shouldDisplayDQICell = isEnabled('data_quality_insights');
  const canEdit = isGranted('pim_enrich_attributegroup_edit');

  return (
    <Table.Row
      key={attributeGroup.code}
      isSelected={isSelected}
      onSelectToggle={selected => onSelectionChange(attributeGroup, selected)}
      onClick={canEdit ? handleRowClick : undefined}
      {...rest}
    >
      <Table.Cell rowTitle={true}>{getLabel(attributeGroup.labels, catalogLocale, attributeGroup.code)}</Table.Cell>
      <Table.Cell>{attributeGroup.attribute_count}</Table.Cell>
      {shouldDisplayDQICell && (
        <Table.Cell>
          <Badge level={attributeGroup.is_dqi_activated ? 'primary' : 'danger'}>
            {translate(
              `akeneo_data_quality_insights.attribute_group.${
                attributeGroup.is_dqi_activated ? 'activated' : 'disabled'
              }`
            )}
          </Badge>
        </Table.Cell>
      )}
    </Table.Row>
  );
});

export {AttributeGroupRow};
