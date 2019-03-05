import DropdownMenu, {DropdownMenuElement} from 'akeneoreferenceentity/application/component/app/dropdown-menu';
import * as React from 'react';
import {mount} from 'enzyme';

const elements = [
  {
    code: 'first_tab',
    label: 'actually this is the first tab',
  },
  {
    code: 'second_tab',
    label: 'and this is the second tab',
  },
];

describe('>>>COMPONENT --- dropdown menu', () => {
  test('Display a simple dropdown menu with a label', () => {
    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        selectedElement={'second_tab'}
      />
    );
    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown menu');
  });

  test('Opens on click, list options and change value on selection', () => {
    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        selectedElement={'second_tab'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown menu');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknSecondaryActions-button').simulate('click');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);

    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('click');
    expect(dropdown.find('.AknDropdown.open').length).toEqual(0);
    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown menu');
  });

  test('Closes when backdrop is clicked', () => {
    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        selectedElement={'second_tab'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown menu');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknSecondaryActions-button').simulate('click');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);
    dropdown.find('.AknDropdown-mask').simulate('click');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
  });

  test('Opens on keypress on space, list options and change value on selection', () => {
    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        selectedElement={'second_tab'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown menu');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknSecondaryActions-button').simulate('keypress', {key: ' '});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);

    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('keypress', {key: ' '});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown menu');
  });

  test("doesn't open on keypress on other key, list options and change value on selection", () => {
    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        selectedElement={'second_tab'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown menu');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknSecondaryActions-button').simulate('keypress', {key: ''});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknSecondaryActions-button').simulate('keypress', {key: ' '});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);

    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('keypress', {key: ''});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);
  });

  test('Change value on selection change', () => {
    let value = 'second_tab';
    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={(newValue: DropdownMenuElement) => {
          value = newValue.code;
        }}
        selectedElement={value}
      />
    );

    expect(value).toEqual('second_tab');
    dropdown.find('.AknSecondaryActions-button').simulate('click');
    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('click');
    expect(value).toEqual('first_tab');
  });

  test('No update on value if same value', () => {
    let value = 'first_tab';
    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={(newValue: DropdownMenuElement) => {
          value = newValue.code;
        }}
        selectedElement={'second_tab'}
      />
    );

    expect(value).toEqual('first_tab');
    dropdown.find('.AknSecondaryActions-button').simulate('click');
    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('click');
    expect(value).toEqual('first_tab');
  });

  test("Doesn't display anything if no elements passed", () => {
    const dropdown = mount(
      <DropdownMenu elements={[]} label={'my dropdown menu'} onSelectionChange={() => {}} selectedElement={'tab'} />
    );

    expect(dropdown.find('.AknSecondaryActions-button').length).toEqual(1);
    expect(dropdown.find('.AknDropdown-menuLink').length).toEqual(0);
  });

  test('Uses the given custom button view', () => {
    const ButtonView = () => {
      return <span className="myCustomClass">Custom button</span>;
    };

    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        ButtonView={ButtonView}
        selectedElement={'second_tab'}
      />
    );

    expect(dropdown.find('.myCustomClass').length).toEqual(1);
    expect(dropdown.find('.myCustomClass').text()).toEqual('Custom button');
  });

  test('Uses the given custom item view', () => {
    const ItemView = ({element}: {element: DropdownMenuElement}) => {
      return <li>{element.label}</li>;
    };

    const dropdown = mount(
      <DropdownMenu
        elements={elements}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        ItemView={ItemView}
        selectedElement={'second_tab'}
      />
    );

    expect(dropdown.find('li').length).toEqual(2);
  });

  test('Uses the custom class on the Dropdown menu', () => {
    const dropdown = mount(
      <DropdownMenu
        elements={[]}
        label={'my dropdown menu'}
        onSelectionChange={() => {}}
        selectedElement={'tab'}
        className={'myCustomClass'}
      />
    );

    expect(dropdown.find('.myCustomClass').length).toEqual(2);
  });
});
