import React from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ImageUploader} from './ImageUploader';
import {pimTheme} from 'akeneo-design-system';

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
    <ThemeProvider theme={pimTheme}>
      <BrandingForm {...props} />
    </ThemeProvider>
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
