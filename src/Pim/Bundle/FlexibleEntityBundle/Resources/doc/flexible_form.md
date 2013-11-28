Create flexible entity form
===========================

By extending basic form types, you can quickly create a create / edit form for your flexible entity.

Create flexible entity form
---------------------------

```php
<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Pim\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends FlexibleType
{
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);

        // another classic doctrine property, you can add here custom doctrine mapping too
        $builder->add('sku', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'acme_product';
    }
}
```

Create flexible value form
--------------------------

```php
<?php
class ProductValueType extends FlexibleValueType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'acme_product_value';
    }
}
```

Declare forms as services
-------------------------

```yaml
    form.type.acme_product:
        class: Acme\Bundle\DemoFlexibleEntityBundle\Form\Type\ProductType
        arguments: [@product_manager, 'acme_product_value']
        tags:
            - { name: form.type, alias: acme_product }

    form.type.acme_product_value:
        class: Acme\Bundle\DemoFlexibleEntityBundle\Form\Type\ProductValueType
        arguments: [@product_manager, @pim_flexibleentity.value_form.value_subscriber]
        tags:
            - { name: form.type, alias: acme_product_value }
```

Use from controller
-------------------

```php
    public function editAction(Product $entity)
    {
        $request = $this->getRequest();

        $form = $this->createForm('acme_product', $entity);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $em = $this->getProductManager()->getStorageManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product successfully saved');

                return $this->redirect($this->generateUrl('acme_demoflexibleentity_product_list'));
            }
        }

        return array('form' => $form->createView(),);
    }
```

