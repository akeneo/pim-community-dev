import React from 'react';
import SelectAttributeType from './SelectAttributeType';
import {Button, useBooleanState} from 'akeneo-design-system';
import {Modal} from 'akeneo-design-system';
import {CreateAttributeProgressIndicator} from './CreateAttributeProgressIndicator';

export type CreateAttributeButtonStepProps = {
  onClose: () => void;
  onStepConfirm: (data: AttributeData) => void;
  initialData?: AttributeData;
  onBack?: () => void;
  children?: React.ReactNode;
};

export type CreateAttributeButtonAppProps = {
  buttonTitle: string;
  iconsMap: {[attributeType: string]: string};
  steps: {[attributeType: string]: {view: React.FC<CreateAttributeButtonStepProps>}[]};
  isModalOpen?: boolean;
  onClick: (data: AttributeData) => void;
  initialData?: AttributeData;
};

type AttributeType = string;

export type AttributeData = {
  attribute_type?: AttributeType;
} & {[key: string]: any};

const CreateAttributeButtonApp: React.FC<CreateAttributeButtonAppProps> = ({
  buttonTitle,
  steps,
  iconsMap,
  isModalOpen = false,
  onClick,
  initialData = {},
}) => {
  const [isOpen, open, close] = useBooleanState(isModalOpen);
  const [attributeData, setAttributeData] = React.useState<AttributeData>(initialData);
  const [currentStepIndex, setCurrentStepIndex] = React.useState<number>(-1);

  let stepsForAttributeType = steps.default;
  if (attributeData.attribute_type) {
    if (attributeData.attribute_type in steps) {
      stepsForAttributeType = steps[attributeData.attribute_type];
    }
  }

  const handleStepConfirm = (data: AttributeData) => {
    const newData = {...attributeData, ...data};
    setAttributeData(newData);
    if (currentStepIndex + 1 === Object.keys(stepsForAttributeType).length) {
      close();
      onClick(newData);

      return;
    }

    setCurrentStepIndex(currentStepIndex + 1);
  };

  const handleClose = () => {
    setCurrentStepIndex(-1);
    setAttributeData(initialData);
    close();
  };

  const handleBack = () => {
    setCurrentStepIndex(currentStepIndex - 1);
  };

  return (
    <>
      {isOpen && (
        <>
          <Modal closeTitle={''} onClose={() => {}} />
          {currentStepIndex === -1 && (
            <SelectAttributeType onClose={handleClose} iconsMap={iconsMap} onStepConfirm={handleStepConfirm}>
              <CreateAttributeProgressIndicator
                currentStepIndex={currentStepIndex}
                selectedType={attributeData?.attribute_type}
              />
            </SelectAttributeType>
          )}
          {stepsForAttributeType.map((step, stepIndex) => {
            const Component = step.view;

            return (
              stepIndex === currentStepIndex && (
                <Component
                  key={stepIndex}
                  onClose={handleClose}
                  onBack={handleBack}
                  onStepConfirm={handleStepConfirm}
                  initialData={attributeData}
                />
              )
            );
          })}
        </>
      )}
      <Button id="attribute-create-button" onClick={open}>
        {buttonTitle}
      </Button>
    </>
  );
};

export {CreateAttributeButtonApp};
