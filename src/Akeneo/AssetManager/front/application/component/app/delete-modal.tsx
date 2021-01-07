import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {Key} from 'akeneo-design-system';

interface Props {
  message: string;
  title: string;
  onConfirm: () => void;
  onCancel: () => void;
}

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
              <div className="AknButtonList">
                <button
                  ref={this.cancelButton}
                  className="AknButtonList-item AknButton AknButton--grey cancel"
                  onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
                    if (Key.Space === event.key) onCancel();
                  }}
                  onClick={onCancel}
                >
                  {__('pim_asset_manager.modal.delete.button.cancel')}
                </button>

                <button className="AknButtonList-item AknButton AknButton--important ok" onClick={onConfirm}>
                  {__('pim_asset_manager.modal.delete.button.confirm')}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default DeleteModal;
