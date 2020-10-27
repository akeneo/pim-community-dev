import React from 'react';
import styled from 'styled-components';
// @todo pull-up master: change to '@akeneo-pim-community/shared'
import {AkeneoThemeProvider} from 'akeneosharedcatalog/akeneo-pim-community/shared';
// @todo pull-up master: change to '@akeneo-pim-community/legacy-bridge'
import {DependenciesProvider, useTranslate} from 'akeneosharedcatalog/akeneo-pim-community/legacy-bridge';
import {ImageUploader} from './ImageUploader';

const Container = styled.div`
  margin-top: 40px;
`;

type Branding = {
  image: string | null;
};

type BrandingError = {
  image: string;
};

type BrandingProps = {
  branding: Branding;
  validationErrors: BrandingError[];
  onBrandingChange: (updatedBranding: Branding) => void;
};

const Branding = (props: BrandingProps) => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <BrandingForm {...props} />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

const BrandingForm = ({branding, validationErrors, onBrandingChange}: BrandingProps) => {
  const translate = useTranslate();

  return (
    <Container>
      <ImageUploader
        image={branding.image}
        validationErrors={validationErrors.map(error => translate(error.image))}
        onChange={image => onBrandingChange({...branding, image})}
      />
    </Container>
  );
};

export {Branding};
