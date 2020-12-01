import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {RemoveButton} from 'akeneoassetmanager/application/component/app/remove-button';

//TODO RAC-414 use DSM IconButton, trash this file
test('It should display a remove button', () => {
  const title = 'pim_asset_manager.asset_picker.basket.remove_one_asset';
  const onRemove = jest.fn();

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <RemoveButton title={title} onClick={() => onRemove} />
    </ThemeProvider>
  );

  expect(container.querySelector(`button[title="${title}"]`)).toBeInTheDocument();
});

test('It should apply an action when we click on the button', () => {
  const title = 'pim_asset_manager.asset_picker.basket.remove_one_asset';
  let removeButtonClicked = false;

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <RemoveButton
        title={title}
        onClick={() => {
          removeButtonClicked = true;
        }}
      />
    </ThemeProvider>
  );

  fireEvent.click(container.querySelector(`button[title="${title}"]`));
  expect(removeButtonClicked).toEqual(true);
});
