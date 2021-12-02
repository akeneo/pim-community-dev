import * as React from 'react';
import {FC, useEffect, useRef} from 'react';
import {Button, Helper, Modal} from 'akeneo-design-system';
import {useTranslate, View} from "@akeneo-pim-community/shared";

enum Steps {
  Choose = 'choose',
  Configure = 'configure',
  Confirm = 'confirm',
}

type FormData = {
  operation: string;
  itemsCount: number;
}

type Props = {
  getStep: () => View;
  currentStep: Steps;
  formData: FormData;
  closeModal: () => void;
  selectBulkAction: (bulkActionCode: string) => void;
  confirmBulkAction: () => void;
  submitBulkAction: () => void;
  chooseBulkAction: () => void;
};

const BulkActionsLauncher: FC<Props> = ({getStep, currentStep, formData, closeModal, selectBulkAction, confirmBulkAction, submitBulkAction, chooseBulkAction}) => {
  const translate = useTranslate();
  const stepRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (stepRef.current) {
      stepRef.current.append(getStep().render().el);
    }
  }, []);

  return (
    <Modal closeTitle={'Close modal'} onClose={closeModal}>
      <Modal.SectionTitle color="brand">PRODUCTS BULK ACTIONS</Modal.SectionTitle>
      <Modal.Title>Select your action</Modal.Title>

      {
        currentStep === Steps.Configure &&
          <>
            <Modal.TopLeftButtons>
              <Button level="tertiary" onClick={() => chooseBulkAction()}>Previous step</Button>
            </Modal.TopLeftButtons>
            <Modal.TopRightButtons>
              <Button level="primary" onClick={() => confirmBulkAction()}>Next step</Button>
            </Modal.TopRightButtons>
          </>
      }

      {
        currentStep === Steps.Confirm &&
          <>
            <Modal.TopLeftButtons>
                <Button level="tertiary" onClick={() => selectBulkAction(formData.operation)}>Next step</Button>
            </Modal.TopLeftButtons>
            <Helper level="warning">
              {translate('pim_enrich.mass_edit.product.confirm', {itemsCount: formData.itemsCount}, formData.itemsCount)}
            </Helper>
            <Button level="primary" onClick={() => submitBulkAction()}>Apply changes</Button>
          </>
      }

      <div ref={stepRef}/>

      <div className="AknFullPage-bottom">
        <div className="AknSteps">
            <div className="AknSteps-step AknSteps-step--checked">
                <div className="AknSteps-stepCircle"/>
                Select an action
            </div>
            {currentStep === Steps.Choose ?
              <>
                <div className="AknSteps-step AknSteps-step--undefined">
                    <div className="AknSteps-stepCircle"/>
                </div>
                <div className="AknSteps-step AknSteps-step--undefined">
                    <div className="AknSteps-stepCircle"/>
                </div>
              </>
                :
              <>
                <div className="AknSteps-step AknSteps-step--checked">
                    <div className="AknSteps-stepCircle"/>
                    {getStep().getLabel()}
                </div>
                <div className={`AknSteps-step ${currentStep === Steps.Confirm ? "AknSteps-step--checked" : ""}`}>
                    <div className="AknSteps-stepCircle"/>
                    Confirm your action
                </div>
              </>
            }
        </div>
      </div>
    </Modal>
  );
}

export {BulkActionsLauncher};
