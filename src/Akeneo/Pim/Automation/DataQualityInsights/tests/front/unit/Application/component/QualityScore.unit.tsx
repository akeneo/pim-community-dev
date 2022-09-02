import {QualityScore} from '@akeneo-pim-community/data-quality-insights/src/application/component/QualityScore';
import {render, screen} from '@testing-library/react';
import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

test('it renders a quality score equal to A', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toBeInTheDocument();
  expect(screen.getByText('A')).toHaveStyle({
    color: `${pimTheme.color.green120}`,
    backgroundColor: `${pimTheme.color.green20}`,
  });
});

test('it renders a quality score equal to A in normal size', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} size={'normal'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({height: '20px', width: '20px'});
});

test('it renders a quality score equal to A in big size', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} size={'big'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({height: '25px', width: '25px'});
});

test('it renders a quality score equal to A left rounded', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} rounded={'left'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({borderRadius: '4px 0 0 4px'});
});

test('it renders a quality score equal to A right rounded', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} rounded={'right'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({borderRadius: '0 4px 4px 0'});
});

test('it renders a quality score equal to A rounded', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} rounded={'all'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({borderRadius: '4px'});
});

test('it renders a quality score equal to A squared', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} rounded={'none'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({borderRadius: '0'});
});

test('it renders a quality score equal to A stacked in normal size', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} stacked />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '2px',
    left: '0',
  });
  const emptyContainerMiddle = screen.getByTestId('empty-container-middle');
  expect(emptyContainerMiddle).toBeInTheDocument();
  expect(emptyContainerMiddle).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '0',
    left: '2px',
  });
  const emptyContainerBack = screen.getByTestId('empty-container-back');
  expect(emptyContainerBack).toBeInTheDocument();
  expect(emptyContainerBack).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '-2px',
    left: '4px',
  });
});

test('it renders a quality score equal to A stacked in big size', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={'A'} size={'big'} stacked />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('A')).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '2px',
    left: '2px',
  });
  const emptyContainerMiddle = screen.getByTestId('empty-container-middle');
  expect(emptyContainerMiddle).toBeInTheDocument();
  expect(emptyContainerMiddle).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '0',
    left: '2px',
  });
  const emptyContainerBack = screen.getByTestId('empty-container-back');
  expect(emptyContainerBack).toBeInTheDocument();
  expect(emptyContainerBack).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '-2px',
    left: '4px',
  });
});
