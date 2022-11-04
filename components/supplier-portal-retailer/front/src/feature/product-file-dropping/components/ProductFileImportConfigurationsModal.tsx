import React, {useState} from 'react';
import {Button, Field, ImportXlsxIllustration, Modal, SelectInput, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useProductFileImports} from '../hooks';
import {ProductFileImportConfiguration} from '../models/read/ProductFileImportConfiguration';

const ProductFileImportConfigurationsModal = () => {
    const translate = useTranslate();
    const [isModalOpen, openModal, closeModal] = useBooleanState(false);
    const [selectedProfile, setProfile] = useState<string | null>(null);
    const {productFileImportConfigurations} = useProductFileImports(isModalOpen);

    const onClose = () => {
        setProfile(null);
        closeModal();
    };

    return (
        <>
            <Button level={'primary'} onClick={openModal}>
                {translate('supplier_portal.product_file_dropping.supplier_files.discussion.import_file_button_label')}
            </Button>
            {isModalOpen && (
                <Modal onClose={onClose} closeTitle="" illustration={<ImportXlsxIllustration />}>
                    <Modal.SectionTitle color="brand">
                        {translate('supplier_portal.product_file_dropping.supplier_files.product_files_modal.title')}
                    </Modal.SectionTitle>
                    <Modal.Title>
                        {translate('supplier_portal.product_file_dropping.supplier_files.product_files_modal.subtitle')}
                    </Modal.Title>
                    <Field
                        label={translate(
                            'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_field_label'
                        )}
                    >
                        <SelectInput
                            emptyResultLabel=""
                            value={selectedProfile}
                            onChange={setProfile}
                            placeholder={translate(
                                'supplier_portal.product_file_dropping.supplier_files.product_files_modal.select_import_placeholder'
                            )}
                            openLabel=""
                        >
                            {productFileImportConfigurations &&
                                productFileImportConfigurations.map(
                                    (productFileImportConfiguration: ProductFileImportConfiguration) => (
                                        <SelectInput.Option
                                            value={productFileImportConfiguration.code}
                                            key={productFileImportConfiguration.code}
                                        >
                                            {productFileImportConfiguration.label}
                                        </SelectInput.Option>
                                    )
                                )}
                        </SelectInput>
                    </Field>
                    <Modal.BottomButtons>
                        <Button level="tertiary" onClick={onClose}>
                            {translate('pim_common.cancel')}
                        </Button>
                        <Button level="primary" onClick={() => {}} disabled={null === selectedProfile}>
                            {translate(
                                'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_button_label'
                            )}
                        </Button>
                    </Modal.BottomButtons>
                </Modal>
            )}
        </>
    );
};

export {ProductFileImportConfigurationsModal};
