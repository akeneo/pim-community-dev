import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import FileDropZone from 'akeneoassetmanager/application/asset-upload/component/file-drop-zone';

describe('Test file-drop-zone component', () => {
  test('It renders without errors', () => {
    renderWithProviders(<FileDropZone onDrop={jest.fn()} />);

    expect(screen.getByText('pim_asset_manager.asset.upload.drop_or_click_here')).toBeInTheDocument();
  });

  test('It allows me to upload several files in the input field', () => {
    let result: string[] = [];
    const onDrop = (event: React.ChangeEvent<HTMLInputElement>) => {
      if (event.target?.files) {
        result = Array.from(event.target.files).map((file: File) => file.name);
      }
    };

    renderWithProviders(<FileDropZone onDrop={onDrop} />);

    const files = [
      new File(['foo'], 'foo.png', {type: 'image/png'}),
      new File(['bar'], 'bar.png', {type: 'image/png'}),
    ];

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.drop_or_click_here');
    fireEvent.change(input, {target: {files}});

    expect(result).toEqual(['foo.png', 'bar.png']);
  });
});
