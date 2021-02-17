import React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {Button} from 'akeneo-design-system';
import {ButtonContainer} from './button';

interface Props {
  message: string;
  title: string;
  onConfirm: () => void;
  onCancel: () => void;
}

//TODO Use DSM Modal
class DeleteModal extends React.Component<Props> {
  private cancelButton: React.RefObject<HTMLButtonElement>;

  constructor(props: Props) {
    super(props);

    this.cancelButton = React.createRef();
  }

  componentDidMount() {
    if (null !== this.cancelButton.current) {
      this.cancelButton.current.focus();
    }
  }

  render() {
    const {message, title, onConfirm, onCancel} = this.props;

    return (
      <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
        <div className="AknFullPage">
          <div className="AknFullPage-content AknFullPage-content--withIllustration">
            <div>
              <div className="AknFullPage-image AknFullPage-illustration AknFullPage-illustration--delete" />
            </div>
            <div>
              <div className="AknFullPage-titleContainer">
                <div className="AknFullPage-subTitle">{title}</div>
                <div className="AknFullPage-title">{__('pim_asset_manager.modal.delete.subtitle')}</div>
                <div className="AknFullPage-description AknFullPage-description--bottom">{message}</div>
              </div>
              <ButtonContainer>
                <Button ref={this.cancelButton} level="tertiary" onClick={onCancel}>
                  {__('pim_asset_manager.modal.delete.button.cancel')}
                </Button>
                <Button level="danger" onClick={onConfirm}>
                  {__('pim_asset_manager.modal.delete.button.confirm')}
                </Button>
              </ButtonContainer>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default DeleteModal;
