<?php

namespace Pim\Bundle\UserBundle\Form\Subscriber;

use Akeneo\Tool\Component\Localization\Provider\LocaleProviderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\LocaleRepository;
use Pim\Bundle\EnrichBundle\Form\Type\LightEntityType;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Pim\Component\User\Model\UserInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Subscriber to override additional user fields with regular entity fields and use custom query builders
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesSubscriber implements EventSubscriberInterface
{
    /** @var LocaleProviderInterface */
    protected $localeProvider;

    /** @var TranslatedLabelsProviderInterface */
    protected $categoryRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /**
     * @param LocaleProviderInterface               $localeProvider
     * @param TranslatedLabelsProviderInterface     $categoryRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        LocaleProviderInterface $localeProvider,
        TranslatedLabelsProviderInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->localeProvider = $localeProvider;
        $this->categoryRepository = $categoryRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
    }

    /**
     * Override catalogLocale, catalogScope and defaultTree fields
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $user = $event->getData();
        if (!$user instanceof UserInterface) {
            return;
        }

        $form = $event->getForm();

        $this->updateCatalogLocale($form);
        $this->updateCatalogScope($form);
        $this->updateDefaultTree($form);
        $this->updateUiLocale($form, $user);
    }

    /**
     * @param FormInterface $form
     */
    protected function updateCatalogLocale(FormInterface $form)
    {
        $form->add(
            'catalogLocale',
            EntityType::class,
            [
                'class'         => 'PimCatalogBundle:Locale',
                'choice_label'  => 'code',
                'select2'       => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->getActivatedLocalesQB();
                },
            ]
        );
    }

    /**
     * @param FormInterface $form
     */
    protected function updateCatalogScope(FormInterface $form)
    {
        $form->add(
            'catalogScope',
            EntityType::class,
            [
                'class'        => 'PimCatalogBundle:Channel',
                'choice_label' => 'label',
                'select2'      => true
            ]
        );
    }

    /**
     * @param FormInterface $form
     */
    protected function updateDefaultTree(FormInterface $form)
    {
        $form->add(
            'defaultTree',
            LightEntityType::class,
            [
                'select2'    => true,
                'repository' => $this->categoryRepository,
            ]
        );
    }

    /**
     * @param FormInterface                           $form
     * @param \Pim\Component\User\Model\UserInterface $user
     */
    protected function updateUiLocale(FormInterface $form, UserInterface $user)
    {
        $uiLocale = $user->getUiLocale();
        if (null === $uiLocale) {
            $uiLocale = $this->localeRepository->findOneByIdentifier('en_US');
        }

        $localeProvider = $this->localeProvider;
        $form->add(
            'uiLocale',
            EntityType::class,
            [
                'class'         => 'PimCatalogBundle:Locale',
                'choice_label'  => 'getName',
                'select2'       => true,
                'data'          => $uiLocale,
                'query_builder' => function (LocaleRepository $repository) use ($localeProvider) {
                    $locales = $localeProvider->getLocales();

                    return $repository->createQueryBuilder('l')
                        ->where('l.code IN (:locales)')
                        ->setParameter('locales', array_keys($locales));
                },
            ]
        );
    }
}
