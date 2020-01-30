import React from "react";
import Modal from "../../Modal";
import useFetchFamilies from "../../../../infrastructure/hooks/useFetchFamilies";
import Family from "../../../../domain/Family.interface";
import {Select2} from "../../select2";

const __ = require('oro/translator');
const UserContext = require('pim/user-context');

interface FamilyModalProps {
  onConfirm: () => void;
  onDismissModal: () => void;
  onSelectFamily: (familyCodes: string[]) => void;
  isVisible: boolean;
  canAddMoreFamilies: boolean;
  errorMessage: string;
}

const FamilyModal = ({onConfirm, onDismissModal, onSelectFamily, isVisible, canAddMoreFamilies, errorMessage}: FamilyModalProps) => {

  const uiLocale = UserContext.get('uiLocale');
  const families: Family[] = useFetchFamilies(isVisible, uiLocale);

  if (!isVisible) {
    return (<></>);
  }

  const select2Configuration = {
    placeholder: ' ',
    allowClear: true,
    dropdownCssClass: 'select2--annotedLabels',
    multiple: true,
    data: Object.values(families).map((family) => ({
      id: family.code,
      text: family.labels[uiLocale] ? family.labels[uiLocale] : "[" + family.code + "]"
    }))
  };

  let modalContent =
    <div>
      {!canAddMoreFamilies && (
        <div className="AknMessageBox AknMessageBox--error AknMessageBox--withIcon">
          {errorMessage}
        </div>
      )}
      <div>{__('pim_enrich.entity.family.plural_label')} :</div>
      <Select2 configuration={select2Configuration} onChange={onSelectFamily}/>
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
