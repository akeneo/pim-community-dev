import * as React from 'react';
import {Button, Modal, useBooleanState} from 'akeneo-design-system';
import styled from "styled-components";
import {PimView, useRouter} from "@akeneo-pim-community/shared";
import {FC, useState} from "react";

type BulkAction = {
  extensionCode: string;
  code: string;
  icon: string;
  label: string;
};
type Props = {
  bulkActions: BulkAction[];
  parent: any;
  formData: any;
};

const BulkActionsLauncher: FC<Props> = ({bulkActions, parent, formData}) => {
  const [isModalOpen, openModal, closeModal] = useBooleanState(false);
  const [chosenBulkAction, chooseBulkAction] = useState<BulkAction | null>(null);
  const router = useRouter();

  const onCloseModal = () => {
    closeModal();
    chooseBulkAction(null);
    router.redirectToRoute('pim_enrich_product_index');
  }

  return (
    <>
      <StyledButton level={"secondary"} onClick={openModal}>Bulk actions 2</StyledButton>
      {isModalOpen &&
        <Modal closeTitle={'Close modal'} onClose={onCloseModal}>
          <Modal.SectionTitle color="brand">PRODUCTS BULK ACTIONS</Modal.SectionTitle>
          <Modal.Title>Select your action</Modal.Title>
          {!chosenBulkAction && <BulkActionsGrid>
            {
              bulkActions.map(bulkAction => {
                return <div key={bulkAction.code}>
                  <img src={`/bundles/pimui/images/bulk/${bulkAction.icon}.svg`} className="AknSquareList-icon"/>
                  <div onClick={() => chooseBulkAction(bulkAction)}>{bulkAction.label}</div>
                </div>;
              })
            }
          </BulkActionsGrid>
          }
          {chosenBulkAction && <PimView viewName={chosenBulkAction.extensionCode} parent={parent} data={formData}/>}
        </Modal>
      }
    </>
  );
}

const BulkActionsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(5, 128px);
`;

const StyledButton = styled(Button)`
  margin-right: 10px;
`;

export {BulkActionsLauncher};
