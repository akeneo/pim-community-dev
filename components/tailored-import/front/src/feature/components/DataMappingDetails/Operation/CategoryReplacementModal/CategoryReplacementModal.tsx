import {ReplacementValues} from "../../../../models";
import {formatParameters, getLabel, NotificationLevel, useNotify, useTranslate, useUserContext, ValidationError} from "@akeneo-pim-community/shared";
import React, {useEffect, useState} from "react";
import {useCategoryTrees} from "../../../../hooks/useCategoryTrees";
import {Button, Modal, TabBar, Table} from "akeneo-design-system";
import styled from "styled-components";
import {CategoryTree} from "../../../../models/Category";
import {CategoryReplacementList} from "./CategoryReplacementList";
import {CATEGORY_REPLACEMENT_OPERATION_TYPE} from "../Block";
import {useRoute} from "@akeneo-pim-community/shared/lib/hooks/useRoute";
import {filterErrors} from "@akeneo-pim-community/shared/lib/models/validation-error";

const Container = styled.div`
  width: 100%;
  max-height: 100vh;
  padding-top: 40px;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const Content = styled.div`
  display: flex;
  flex-direction: column;
  width: 100%;
  flex: 1;
  overflow: auto;
  overflow-x: hidden;
`;

type CategoryReplacementModalProps = {
  initialMapping: ReplacementValues;
  onConfirm: (updatedReplacementValues: ReplacementValues) => void;
  onCancel: () => void;
};

const CategoryReplacementModal = ({
  initialMapping,
  onConfirm,
  onCancel,
}: CategoryReplacementModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const validateReplacementOperationRoute = useRoute('pimee_tailored_import_validate_replacement_operation_action');
  const catalogLocale = useUserContext().get('catalogLocale');
  const categoryTrees = useCategoryTrees();
  const [activeCategoryTree, setActiveCategoryTree] = useState<number|null>(null);
  const [mapping, setMapping] = useState(initialMapping);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);

  useEffect(() => {
    if (categoryTrees.length > 0) {
      setActiveCategoryTree(categoryTrees[0].id);
    }
  }, [categoryTrees]);

  const handleConfirm = async () => {
    setValidationErrors([]);
    const response = await fetch(validateReplacementOperationRoute, {
      body: JSON.stringify({
        type: CATEGORY_REPLACEMENT_OPERATION_TYPE,
        mapping,
      }),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    });

    if (response.ok) {
      onConfirm(mapping);
    } else {
      try {
        const errors = await response.json();

        setValidationErrors(formatParameters(errors));
      } catch (error) {}

      notify(
        NotificationLevel.ERROR,
        translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.validation_error')
      );
    }
  }

  function handleActiveCategoryTreeChange(tree: CategoryTree) {
    setActiveCategoryTree(tree.id);
  }

  const displayedCategoryTree = categoryTrees.find((categoryTree) => categoryTree.id === activeCategoryTree);
  if (!displayedCategoryTree) {
    return null;
  }

  return (
    <Modal onClose={onCancel} closeTitle={translate('pim_common.close')}>
      <Modal.TopRightButtons>
        <Button level="primary" onClick={handleConfirm}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.TopRightButtons>
      <Container>
        <Modal.SectionTitle color="brand">
          {translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.subtitle')}
        </Modal.SectionTitle>
        <Modal.Title>{translate('akeneo.tailored_import.data_mapping.operations.category_replacement.modal.title')}</Modal.Title>
        <Content>
          <TabBar moreButtonTitle={translate('pim_common.more')}>
            {categoryTrees.map(tree => (
              <TabBar.Tab
                isActive={activeCategoryTree === tree.id}
                key={tree.id}
                onClick={() => handleActiveCategoryTreeChange(tree)}
              >
                {getLabel(tree.labels, catalogLocale, tree.code)}
              </TabBar.Tab>
            ))}
          </TabBar>
          <Table>
            <Table.Header sticky={0}>
              <Table.HeaderCell>
                {translate(
                  'akeneo.tailored_import.data_mapping.operations.category_replacement.modal.table.header.replacement'
                )}
              </Table.HeaderCell>
              <Table.HeaderCell>
                {translate(
                  'akeneo.tailored_import.data_mapping.operations.category_replacement.modal.table.header.source_values'
                )}
              </Table.HeaderCell>
            </Table.Header>
            <Table.Body>
              <CategoryReplacementList
                categoryTree={displayedCategoryTree}
                mapping={mapping}
                onMappingChange={setMapping}
                validationErrors={filterErrors(validationErrors, '[mapping]')}
              />
            </Table.Body>
          </Table>
        </Content>
      </Container>
    </Modal>
  );
};

export {CategoryReplacementModal};
