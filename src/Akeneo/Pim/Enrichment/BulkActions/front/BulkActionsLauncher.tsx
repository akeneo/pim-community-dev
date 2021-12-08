import * as React from 'react';
import {FC, useEffect, useRef} from 'react';
import {Button, Helper, Modal, ProductsIllustration} from 'akeneo-design-system';
import {useTranslate, View} from '@akeneo-pim-community/shared';
import {ChangeStatus} from './BulkActions/ChangeStatus';

enum Steps {
  Choose = 'choose',
  Configure = 'configure',
  Confirm = 'confirm',
}

type FormData = {
  operation: string;
  itemsCount: number;
  actions: any;
};

type Props = {
  getStep: () => View;
  step: View;
  currentStep: Steps;
  formData: FormData;
  setData: (data: any) => void;
  closeModal: () => void;
  selectBulkAction: (bulkActionCode: string) => void;
  confirmBulkAction: () => void;
  submitBulkAction: () => void;
  chooseBulkAction: () => void;
};

const bulkActionsComponentsMapping = {
  change_status: {
    component: ChangeStatus,
    getIllustration: () => <ProductsIllustration />,
  },
};

const BulkActionsLauncher: FC<Props> = ({
  getStep,
  step,
  currentStep,
  formData,
  setData,
  closeModal,
  selectBulkAction,
  confirmBulkAction,
  submitBulkAction,
  chooseBulkAction,
}) => {
  const translate = useTranslate();
  const stepRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (stepRef.current) {
      // clear the previous step content before adding the new one
      while (stepRef.current.firstChild) {
        stepRef.current.removeChild(stepRef.current.firstChild);
      }
      stepRef.current.append(step.render().el);
    }
  }, [currentStep, formData.operation, step]);

  const onConfigureBulkAction = (bulkActionData: any) => {
    const newFormData = {...formData, actions: bulkActionData};
    setData(newFormData);
  };

  let modalIllustration = undefined;
  if (formData.operation && bulkActionsComponentsMapping.hasOwnProperty(formData.operation)) {
    modalIllustration = bulkActionsComponentsMapping[formData.operation].getIllustration();
  }

  return (
    <Modal closeTitle={'Close modal'} onClose={closeModal} illustration={modalIllustration}>
      <Modal.SectionTitle color="brand">PRODUCTS BULK ACTIONS</Modal.SectionTitle>
      <Modal.Title>Select your action</Modal.Title>

      {currentStep === Steps.Configure && (
        <>
          <Modal.TopLeftButtons>
            <Button level="tertiary" onClick={() => chooseBulkAction()}>
              Previous step
            </Button>
          </Modal.TopLeftButtons>
          <Modal.TopRightButtons>
            <Button level="primary" onClick={() => confirmBulkAction()}>
              Next step
            </Button>
          </Modal.TopRightButtons>
        </>
      )}

      {currentStep === Steps.Confirm && (
        <>
          <Modal.TopLeftButtons>
            <Button level="tertiary" onClick={() => selectBulkAction(formData.operation)}>
              Previous step
            </Button>
          </Modal.TopLeftButtons>
          <Helper level="warning">
            {translate('pim_enrich.mass_edit.product.confirm', {itemsCount: formData.itemsCount}, formData.itemsCount)}
          </Helper>
          <Button level="primary" onClick={() => submitBulkAction()}>
            Apply changes
          </Button>
        </>
      )}

      {formData.operation && bulkActionsComponentsMapping.hasOwnProperty(formData.operation) ? (
        React.createElement(bulkActionsComponentsMapping[formData.operation].component, {
          configureBulkAction: onConfigureBulkAction,
          defaultValue: formData.actions[0],
          readOnly: currentStep === Steps.Confirm,
        })
      ) : (
        <div ref={stepRef} />
      )}

      <div className="AknFullPage-bottom">
        <div className="AknSteps">
          <div className="AknSteps-step AknSteps-step--checked">
            <div className="AknSteps-stepCircle" />
            Select an action
          </div>
          {currentStep === Steps.Choose ? (
            <>
              <div className="AknSteps-step AknSteps-step--undefined">
                <div className="AknSteps-stepCircle" />
              </div>
              <div className="AknSteps-step AknSteps-step--undefined">
                <div className="AknSteps-stepCircle" />
              </div>
            </>
          ) : (
            <>
              <div className="AknSteps-step AknSteps-step--checked">
                <div className="AknSteps-stepCircle" />
                {getStep().getLabel()}
              </div>
              <div className={`AknSteps-step ${currentStep === Steps.Confirm ? 'AknSteps-step--checked' : ''}`}>
                <div className="AknSteps-stepCircle" />
                Confirm your action
              </div>
            </>
          )}
        </div>
      </div>
    </Modal>
  );
};

export {BulkActionsLauncher};
