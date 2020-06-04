import React, {useState} from 'react';
import {AttributeOption} from '../model';
import DeleteConfirmationModal from './DeleteConfirmationModal';

interface AttributeOptionItemProps {
    data: AttributeOption;
    selectAttributeOption: (selectedOptionId: number) => void;
    isSelected: boolean;
    deleteAttributeOption: (optionId: number) => void;
}

const ListItem = ({data, selectAttributeOption, isSelected, deleteAttributeOption}: AttributeOptionItemProps) => {
    const [showDeleteConfirmationModal, setShowDeleteConfirmationModal] = useState<boolean>(false);

    const deleteOption = () => {
        setShowDeleteConfirmationModal(false);
        deleteAttributeOption(data.id);
    };

    const cancelDelete = () => {
        setShowDeleteConfirmationModal(false);
    };

    return (
        <>
            <div className={`AknAttributeOption-listItem ${isSelected ? 'AknAttributeOption-listItem--selected' : ''}`} role="attribute-option-item">
                <span className="AknAttributeOption-itemCode" onClick={() => selectAttributeOption(data.id)} role="attribute-option-item-label">
                    {data.code}
                </span>
                <span className="AknAttributeOption-delete-option-icon" onClick={() => setShowDeleteConfirmationModal(true)} role="attribute-option-delete-button"/>
            </div>
            {showDeleteConfirmationModal && (
                <DeleteConfirmationModal
                    attributeOptionCode={data.code}
                    confirmDelete={deleteOption}
                    cancelDelete={cancelDelete}
                />)
            }
        </>
    );
};

export default ListItem;
