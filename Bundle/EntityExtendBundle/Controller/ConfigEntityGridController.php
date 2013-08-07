<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeyCollectionType;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entityextend/entity")
 * @Acl(
 *      id="oro_entityextend",
 *      name="Entity extend manipulation",
 *      description="Entity extend manipulation"
 * )
 */
class ConfigEntityGridController extends Controller
{
    /**
     * @Route(
     *      "/unique-key/{id}",
     *      name="oro_entityextend_entity_unique_key",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entityextend_entity_unique_key",
     *      name="Unique keys",
     *      description="Update entity unique keys",
     *      parent="oro_entityextend"
     * )
     * @Template
     */
    public function uniqueAction(EntityConfigModel $entity)
    {
        /** @var ConfigProvider $configProvider */
        $configProvider = $this->get('oro_entity_config.provider.extend');
        $entityConfig   = $configProvider->getConfig($entity->getClassName());
        $fieldConfigIds = $configProvider->getConfigIds($entity->getClassName());

        $data = $entityConfig->has('unique_key') ? $entityConfig->get('unique_key') : array();

        $request = $this->getRequest();

        $form = $this->createForm(
            new UniqueKeyCollectionType(
                array_filter($fieldConfigIds,
                    function (FieldConfigId $fieldConfigId) {
                        return $fieldConfigId->getFieldType() != 'ref-many';
                    }
                )
            ),
            $data
        );

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $error = false;
                $names = array();
                foreach ($data['keys'] as $key) {
                    if (in_array($key['name'], $names)) {
                        $error = true;
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            sprintf('Name for key should be unique, key "%s" is not unique.', $key['name'])
                        );
                        break;
                    }

                    if (empty($key['name'])) {
                        $error = true;
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Name of key can\'t be empty.'
                        );
                        break;
                    }

                    $names[] = $key['name'];
                }

                if (!$error) {
                    $entityConfig->set('unique_key', $data);
                    $configProvider->persist($entityConfig);
                    $configProvider->flush();

                    return $this->redirect(
                        $this->generateUrl('oro_entityconfig_view', array('id' => $entity->getId()))
                    );
                }
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        return array(
            'form'          => $form->createView(),
            'entity_id'     => $entity->getId(),
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName())
        );
    }
}
