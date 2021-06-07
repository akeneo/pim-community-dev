import React from 'react';
import {
  Modal,
  Tile,
  Tiles,
  useBooleanState,
  AttributeFileIcon,
  ActivityIcon,
  AddAttributeIcon,
  AkeneoIcon,
  ArrowIcon,
  ArrowDownIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  ArrowUpIcon,
  AssetsIcon,
  AssetCollectionIcon,
  AssociateIcon,
  AttributeBooleanIcon,
  AttributeFileIcon,
  AttributeLinkIcon,
  AttributeMultiSelectIcon,
  AttributeNumberIcon,
  AttributePriceIcon,
  AttributeSelectIcon,
  AttributeTextIcon,
  AttributeTextareaIcon,
  BarChartsIcon,
  BookIcon,
  BoxIcon,
  BrokenLinkIcon,
  CaddyIcon,
  CaddyAddIcon,
  CaddyCheckoutIcon,
  CardIcon,
  CategoryIcon,
  CheckIcon,
  CheckPartialIcon,
  CheckRoundIcon,
  ClockIcon,
  CloseIcon,
  CodeIcon,
  ColumnIcon,
  CompareIcon,
  ComponentIcon,
  CopyIcon,
  CreditsIcon,
  DangerIcon,
  DangerPlainIcon,
  DateIcon,
  DeleteIcon,
  DialogIcon,
  DownloadIcon,
  DragDropIcon,
  EditIcon,
  EntityIcon,
  EntityMultiIcon,
  EraseIcon,
  ExpandIcon,
  ExplanationPointIcon,
  FactoryIcon,
  FileIcon,
  FileCsvIcon,
  FileXlsxIcon,
  FiltersIcon,
  FolderIcon,
  FolderInIcon,
  FolderMovedIcon,
  FolderOutIcon,
  FolderPlainIcon,
  FoldersIcon,
  FoldersPlainIcon,
  FullscreenIcon,
  GiftIcon,
  GroupsIcon,
  HelpIcon,
  IdIcon,
  InfoIcon,
  InfoRoundIcon,
  KeyboardIcon,
  KeyIcon,
  LightIcon,
  LinkIcon,
  ListIcon,
  LoaderIcon,
  LocaleIcon,
  LockIcon,
  MailIcon,
  MediaLinkIcon,
  MegaphoneIcon,
  MetricIcon,
  MinifyIcon,
  MoreIcon,
  MoreVerticalIcon,
  NotificationIcon,
  PanelCloseIcon,
  PanelOpenIcon,
  PlayIcon,
  PlusIcon,
  ProductIcon,
  ProductModelIcon,
  PublishIcon,
  RefreshIcon,
  RowIcon,
  SearchIcon,
  SectionIcon,
  SettingsIcon,
  ShopIcon,
  SupplierIcon,
  SystemIcon,
  TagIcon,
  UnpublishIcon,
  UnviewIcon,
  UploadIcon,
  ValueIcon,
  ViewIcon,
  WaveIcon, Button, placeholderStyle
} from "akeneo-design-system";
import { useRouter, useTranslate } from "@akeneo-pim-community/shared";
import styled from "styled-components";

const Icons = {
  'ActivityIcon': ActivityIcon,
  'AddAttributeIcon': AddAttributeIcon,
  'AkeneoIcon': AkeneoIcon,
  'ArrowIcon': ArrowIcon,
  'ArrowDownIcon': ArrowDownIcon,
  'ArrowLeftIcon': ArrowLeftIcon,
  'ArrowRightIcon': ArrowRightIcon,
  'ArrowUpIcon': ArrowUpIcon,
  'AssetsIcon': AssetsIcon,
  'AssetCollectionIcon': AssetCollectionIcon,
  'AssociateIcon': AssociateIcon,
  'AttributeBooleanIcon': AttributeBooleanIcon,
  'AttributeFileIcon': AttributeFileIcon,
  'AttributeLinkIcon': AttributeLinkIcon,
  'AttributeMultiSelectIcon': AttributeMultiSelectIcon,
  'AttributeNumberIcon': AttributeNumberIcon,
  'AttributePriceIcon': AttributePriceIcon,
  'AttributeSelectIcon': AttributeSelectIcon,
  'AttributeTextIcon': AttributeTextIcon,
  'AttributeTextareaIcon': AttributeTextareaIcon,
  'BarChartsIcon': BarChartsIcon,
  'BookIcon': BookIcon,
  'BoxIcon': BoxIcon,
  'BrokenLinkIcon': BrokenLinkIcon,
  'CaddyIcon': CaddyIcon,
  'CaddyAddIcon': CaddyAddIcon,
  'CaddyCheckoutIcon': CaddyCheckoutIcon,
  'CardIcon': CardIcon,
  'CategoryIcon': CategoryIcon,
  'CheckIcon': CheckIcon,
  'CheckPartialIcon': CheckPartialIcon,
  'CheckRoundIcon': CheckRoundIcon,
  'ClockIcon': ClockIcon,
  'CloseIcon': CloseIcon,
  'CodeIcon': CodeIcon,
  'ColumnIcon': ColumnIcon,
  'CompareIcon': CompareIcon,
  'ComponentIcon': ComponentIcon,
  'CopyIcon': CopyIcon,
  'CreditsIcon': CreditsIcon,
  'DangerIcon': DangerIcon,
  'DangerPlainIcon': DangerPlainIcon,
  'DateIcon': DateIcon,
  'DeleteIcon': DeleteIcon,
  'DialogIcon': DialogIcon,
  'DownloadIcon': DownloadIcon,
  'DragDropIcon': DragDropIcon,
  'EditIcon': EditIcon,
  'EntityIcon': EntityIcon,
  'EntityMultiIcon': EntityMultiIcon,
  'EraseIcon': EraseIcon,
  'ExpandIcon': ExpandIcon,
  'ExplanationPointIcon': ExplanationPointIcon,
  'FactoryIcon': FactoryIcon,
  'FileIcon': FileIcon,
  'FileCsvIcon': FileCsvIcon,
  'FileXlsxIcon': FileXlsxIcon,
  'FiltersIcon': FiltersIcon,
  'FolderIcon': FolderIcon,
  'FolderInIcon': FolderInIcon,
  'FolderMovedIcon': FolderMovedIcon,
  'FolderOutIcon': FolderOutIcon,
  'FolderPlainIcon': FolderPlainIcon,
  'FoldersIcon': FoldersIcon,
  'FoldersPlainIcon': FoldersPlainIcon,
  'FullscreenIcon': FullscreenIcon,
  'GiftIcon': GiftIcon,
  'GroupsIcon': GroupsIcon,
  'HelpIcon': HelpIcon,
  'IdIcon': IdIcon,
  'InfoIcon': InfoIcon,
  'InfoRoundIcon': InfoRoundIcon,
  'KeyboardIcon': KeyboardIcon,
  'KeyIcon': KeyIcon,
  'LightIcon': LightIcon,
  'LinkIcon': LinkIcon,
  'ListIcon': ListIcon,
  'LoaderIcon': LoaderIcon,
  'LocaleIcon': LocaleIcon,
  'LockIcon': LockIcon,
  'MailIcon': MailIcon,
  'MediaLinkIcon': MediaLinkIcon,
  'MegaphoneIcon': MegaphoneIcon,
  'MetricIcon': MetricIcon,
  'MinifyIcon': MinifyIcon,
  'MoreIcon': MoreIcon,
  'MoreVerticalIcon': MoreVerticalIcon,
  'NotificationIcon': NotificationIcon,
  'PanelCloseIcon': PanelCloseIcon,
  'PanelOpenIcon': PanelOpenIcon,
  'PlayIcon': PlayIcon,
  'PlusIcon': PlusIcon,
  'ProductIcon': ProductIcon,
  'ProductModelIcon': ProductModelIcon,
  'PublishIcon': PublishIcon,
  'RefreshIcon': RefreshIcon,
  'RowIcon': RowIcon,
  'SearchIcon': SearchIcon,
  'SectionIcon': SectionIcon,
  'SettingsIcon': SettingsIcon,
  'ShopIcon': ShopIcon,
  'SupplierIcon': SupplierIcon,
  'SystemIcon': SystemIcon,
  'TagIcon': TagIcon,
  'UnpublishIcon': UnpublishIcon,
  'UnviewIcon': UnviewIcon,
  'UploadIcon': UploadIcon,
  'ValueIcon': ValueIcon,
  'ViewIcon': ViewIcon,
  'WaveIcon': WaveIcon,
}

const ModalContent = styled.div`
  width: 730px;
`

const LoadingTile = styled(Tile)`
  ${placeholderStyle}
`;

type AttributeType = string;
type AttributeCode = string;

type CreateButtonAppProps = {
  buttonTitle: string;
  iconsMap: {[attributeType: string]: string},
  isModalOpen?: boolean;
  code?: AttributeCode;
}

const CreateButtonApp: React.FC<CreateButtonAppProps> = ({
  buttonTitle,
  iconsMap,
  isModalOpen = false,
  code,
}) => {
  const [isOpen, open, close] = useBooleanState(isModalOpen);
  const translate = useTranslate();
  const Router = useRouter();

  const [attributeTypes, setAttributeTypes] = React.useState<AttributeType[] | undefined>();

  React.useEffect(() => {
    if (isOpen && !attributeTypes) {
      const url = Router.generate('pim_enrich_attribute_type_index');
      fetch(url).then((response) => response.json().then(attributeTypes => {
        const newAttributeTypes = Object.keys(attributeTypes);
        const sortedAttributeTypes = newAttributeTypes.sort((a, b) => {
          return translate(`pim_enrich.entity.attribute.property.type.${a}`).localeCompare(translate(`pim_enrich.entity.attribute.property.type.${b}`));
        });

        setAttributeTypes(sortedAttributeTypes);
      }));
    }
  }, [isOpen]);

  const handleClick = (attributeType: AttributeType) => {
    close();
    const route = Router.generate('pim_enrich_attribute_create', { attribute_type: attributeType, code });
    Router.redirect(route);
  }

  return <>
    {isOpen && attributeTypes && <Modal closeTitle={translate('pim_common.close')} onClose={close}>
      <Modal.SectionTitle color="brand">{translate('pim_enrich.entity.attribute.property.type.choose')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_enrich.entity.attribute.module.create.button')}</Modal.Title>
      <ModalContent>
        <Tiles>
          {attributeTypes.map(attributeType => {
            const componentAsString = iconsMap[attributeType] || 'AddAttributeIcon';
            const Icon = Icons[componentAsString] || AddAttributeIcon;
            return <Tile
              onClick={() => handleClick(attributeType)}
              key={attributeType}
              icon={<Icon/>}
              title={translate(`pim_enrich.entity.attribute.property.type.${attributeType}`)}
            >
              {translate(`pim_enrich.entity.attribute.property.type.${attributeType}`)}
            </Tile>
          })}
        </Tiles>
      </ModalContent>
    </Modal>}
    <span id="attribute-create-button" className="AknButton AknButton--apply AknButtonList-item" onClick={open}>
      {buttonTitle}
    </span>
  </>
}

export { CreateButtonApp }
