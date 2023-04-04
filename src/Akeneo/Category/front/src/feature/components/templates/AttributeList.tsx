import {Table} from "akeneo-design-system";
import {Attribute} from "../../models";
import {getLabelFromAttribute} from "../attributes";
import React, {useCallback, useState} from "react";
import {userContext, useTranslate} from "@akeneo-pim-community/shared";
import styled from "styled-components";

type Props = {
    attributes: Attribute[];
    templateId: string;

    onAttributeSelection: (attribute: Attribute) => void;
};

export const AttributeList = ({attributes, templateId, onAttributeSelection}: Props) => {
    const translate = useTranslate();
    const catalogLocale = userContext.get('catalogLocale');
    const [selectedAttribute, setSelectedAttribute] = useState<Attribute>(attributes[0]);

    const handleRowOnclick = (attribute: Attribute) => {
        console.log('row clicked. attribute label=' + attribute.labels[catalogLocale]);
        console.log('we should highlight and rerender the attribute line');
        setSelectedAttribute(attribute);
        onAttributeSelection(attribute);
    };

    const sortByOrder = useCallback((attribute1: Attribute, attribute2: Attribute): number => {
        if (attribute1.order >= attribute2.order) {
            return 1;
        } else if (attribute1.order < attribute2.order) {
            return -1;
        }
        return 0;
    }, []);

    return (
        <AttributeListContainer>
            <ScrollablePanel>
            <Table>
                <Table.Header sticky={0}>
                    <Table.HeaderCell>{translate('akeneo.category.template_list.columns.header')}</Table.HeaderCell>
                    <Table.HeaderCell>{translate('akeneo.category.template_list.columns.code')}</Table.HeaderCell>
                    <Table.HeaderCell>{translate('akeneo.category.template_list.columns.type')}</Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                    {attributes?.sort(sortByOrder).map((attribute: Attribute) => (
                        <Table.Row
                            key={attribute.uuid}
                            onClick={() => {
                                handleRowOnclick(attribute)
                            }}
                            isSelected={attribute === selectedAttribute}
                        >
                            <Table.Cell rowTitle>{getLabelFromAttribute(attribute, catalogLocale)}</Table.Cell>
                            <Table.Cell>{attribute.code}</Table.Cell>
                            <Table.Cell>{translate(`akeneo.category.template.attribute.type.${attribute.type}`)}</Table.Cell>
                        </Table.Row>
                    ))}
                </Table.Body>
            </Table>
            </ScrollablePanel>
        </AttributeListContainer>
    );
}

const AttributeListContainer = styled.div`
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
`;

const ScrollablePanel = styled.div`
  overflow: auto;
  height: 55vh;
`;
