import React from "react";
import Modal from "../../Modal";
import {FamiliesSelect2} from "./FamiliesSelect2";

const __ = require('oro/translator');

interface FamilyModalProps {
  onConfirm: () => void;
  onDismissModal: () => void;
  onSelectFamily: (familyCodes: string[]) => void;
  isVisible: boolean;
  canAddMoreFamilies: boolean;
  errorMessage: string;
}

const FamilyModal = ({onConfirm, onDismissModal, onSelectFamily, isVisible, canAddMoreFamilies, errorMessage}: FamilyModalProps) => {

  if (!isVisible) {
    return (<></>);
  }

  let modalContent =
    <div>
      {!canAddMoreFamilies && (
        <div className="AknMessageBox AknMessageBox--error AknMessageBox--withIcon">
          {errorMessage}
        </div>
      )}
      <div>{__('pim_enrich.entity.family.plural_label')} :</div>
      <FamiliesSelect2 onChange={onSelectFamily}/>
    </div>;

  return (
    <Modal
      cssClass={'AknDataQualityInsightsFamilyFilter'}
      title={__('akeneo_data_quality_insights.title')}
      subtitle={__('akeneo_data_quality_insights.dqi_dashboard.widgets.family_modal.subtitle')}
      description={__('akeneo_data_quality_insights.dqi_dashboard.widgets.family_modal.message')}
      illustrationLink={'bundles/pimui/images/illustrations/Family.svg'}
      modalContent={modalContent}
      onConfirm={onConfirm}
      onDismissModal={onDismissModal}
      enableSaveButton={canAddMoreFamilies}
    />
  )
};

export default FamilyModal;
