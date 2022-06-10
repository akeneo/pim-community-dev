import {OperationType, ReplacementValues} from "../../../../models";
import {ReplacementValueFilter} from "../ReplacementModal";
import {getLabel, useTranslate, useUserContext} from "@akeneo-pim-community/shared";
import React, {useEffect, useState} from "react";
import {useCategoryTrees} from "../../../../hooks/useCategoryTrees";
import {Button, Modal, TabBar, Table} from "akeneo-design-system";
import styled from "styled-components";

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
  onConfirm: (updatedReplacementValues: ReplacementValues) => void;
  onCancel: () => void;
};

const CategoryReplacementModal = ({
  onConfirm,
  onCancel
}: CategoryReplacementModalProps) => {
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');
  const categoryTrees = useCategoryTrees();
  const [activeCategoryTree, setActiveCategoryTree] = useState<number|null>(null);

  useEffect(() => {
    if (categoryTrees.length > 0) {
      setActiveCategoryTree(categoryTrees[0].id);
    }
  }, [categoryTrees]);

  const handleConfirm = async () => {

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
                onClick={() => setActiveCategoryTree(tree.id)}
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
            </Table.Body>
          </Table>
        </Content>
      </Container>
    </Modal>
  );
};

export {CategoryReplacementModal};
