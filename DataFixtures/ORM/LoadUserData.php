<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Load demo user data (copy / paste from bap demo)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $userRepository;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadAttributes();
        $this->loadUsers();
    }

    /**
     * Load attributes
     *
     * @return void
     */
    public function loadAttributes()
    {
        $this->assertHasRequiredAttributes(array('company', 'gender'));

        if (!$this->findAttribute('website')) {
            $websiteAttribute = $this->createAttribute('oro_flexibleentity_url', 'website');
            $this->persist($websiteAttribute);
        }

        if (!$this->findAttribute('hobby')) {
            $hobbyAttribute = $this->createAttributeWithOptions(
                'oro_flexibleentity_multiselect',
                'hobby',
                self::getHobbies()
            );
            $this->persist($hobbyAttribute);
        }

        if (!$this->findAttribute('cataloglocale')) {
            $localeAttribute = $this->createAttributeWithOptions(
                'oro_flexibleentity_simpleselect',
                'cataloglocale',
                self::getLocales(),
                true,
                'Catalog locale'
            );
            $this->persist($localeAttribute);
        }

        if (!$this->findAttribute('catalogscope')) {
            $localeAttribute = $this->createAttributeWithOptions(
                'oro_flexibleentity_simpleselect',
                'catalogscope',
                self::getScopes(),
                true,
                'Catalog scope'
            );
            $this->persist($localeAttribute);
        }

        $this->flush();
    }

    /**
     * Asserts required attributes were created
     *
     * @param array $attributeCodes
     *
     * @throws \LogicException
     */
    private function assertHasRequiredAttributes($attributeCodes)
    {
        foreach ($attributeCodes as $attributeCode) {
            if (!$this->findAttribute($attributeCode)) {
                throw new \LogicException(
                    sprintf(
                        'Attribute "%s" is missing, please load "%s" fixture before',
                        $attributeCode,
                        'Acme\Bundle\DemoBundle\DataFixtures\ORM\LoadUserAttrData'
                    )
                );
            }
        }
    }

    /**
     * Load users
     *
     * @return void
     */
    public function loadUsers()
    {
        // change existing users (imported by user bundle itself)
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $this->setFlexibleAttributeValueOption($user, 'cataloglocale', $this->generateLocale());
            $this->setFlexibleAttributeValueOption($user, 'catalogscope', $this->generateScope());
            $this->persist($user);
        }

        // create users
        for ($i = 0; $i < 50; ++$i) {
            $firstName = $this->generateFirstName();
            $lastName = $this->generateLastName();
            $middleName = $this->generateMiddleName();
            $birthday = $this->generateBirthday();
            $salary = $this->generateSalary();
            $username = $this->generateUsername($firstName, $lastName);
            $email = $this->generateEmail($firstName, $lastName);
            $company = $this->generateCompany();
            $website = $this->generateWebsite($firstName, $lastName);
            $gender = $this->generateGender();
            $hobbies = $this->generateHobbies();
            $lastVisit = $this->generateLastVisit();
            $locale    = $this->generateLocale();
            $scope     = $this->generateScope();

            $user = $this->createUser(
                $username,
                $email,
                $firstName,
                $lastName,
                $middleName,
                $birthday,
                $company,
                $website,
                $gender,
                $hobbies,
                $lastVisit,
                $locale,
                $scope
            );

            $user->setPlainPassword(uniqid());
            $this->userManager->updatePassword($user);

            $this->persist($user);
        }
        $this->flush();
    }

    /**
     * Creates a user
     *
     * @param string    $username
     * @param string    $email
     * @param string    $firstName
     * @param string    $lastName
     * @param string    $middleName
     * @param \DateTime $birthday
     * @param int       $salary
     * @param string    $company
     * @param string    $website
     * @param string    $gender
     * @param array     $hobbies
     * @param \DateTime $lastVisit
     * @param string    $locale
     * @param string    $scope
     *
     * @return User
     */
    private function createUser($username, $email, $firstName, $lastName, $middleName, $birthday, $company, $website, $gender, array $hobbies, $lastVisit, $locale, $scope) {
        /** @var $user User */
        $user = $this->userManager->createFlexible();

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstname($firstName);
        $user->setLastname($lastName);
        $user->setBirthday($birthday);

        $this->setFlexibleAttributeValue($user, 'company', $company);
        $this->setFlexibleAttributeValueOption($user, 'gender', $gender);
        $this->setFlexibleAttributeValue($user, 'website', $website);
        $this->addFlexibleAttributeValueOptions($user, 'hobby', $hobbies);
        $this->setFlexibleAttributeValueOption($user, 'cataloglocale', $locale);
        $this->setFlexibleAttributeValueOption($user, 'catalogscope', $scope);

        return $user;
    }

    /**
     * Sets a flexible attribute value
     *
     * @param AbstractFlexible $flexibleEntity
     * @param string           $attributeCode
     * @param string           $value
     *
     * @return void
     *
     * @throws \LogicException
     */
    private function setFlexibleAttributeValue(AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
        if ($attribute = $this->findAttribute($attributeCode)) {
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->setData($value);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Sets a flexible attribute value as option with given value
     *
     * @param AbstractFlexible $flexibleEntity
     * @param string           $attributeCode
     * @param string           $value
     *
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValueOption(AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
        if ($attribute = $this->findAttribute($attributeCode)) {
            $option = $this->findAttributeOptionWithValue($attribute, $value);
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->setOption($option);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Adds option values to flexible attribute value
     *
     * @param AbstractFlexible $flexibleEntity
     * @param string           $attributeCode
     * @param array            $values
     *
     * @return void
     * @throws \LogicException
     */
    private function addFlexibleAttributeValueOptions(AbstractFlexible $flexibleEntity, $attributeCode, array $values)
    {
        if ($attribute = $this->findAttribute($attributeCode)) {
            $flexibleValue = $this->getFlexibleValueForAttribute($flexibleEntity, $attribute);
            foreach ($values as $value) {
                $option = $this->findAttributeOptionWithValue($attribute, $value);
                $flexibleValue->addOption($option);
            }
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Finds an attribute option with value
     *
     * @param AbstractAttribute $attribute
     * @param string            $value
     *
     * @return AbstractAttributeOption
     * @throws \LogicException
     */
    private function findAttributeOptionWithValue(AbstractAttribute $attribute, $value)
    {
        /** @var $options \Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
        $options = $this->userManager->getAttributeOptionRepository()->findBy(
            array('attribute' => $attribute)
        );

        $selectedOption = null;
        foreach ($options as $option) {
            if ($value == $option->getOptionValue()->getValue()) {
                return $option;
            }
        }

        throw new \LogicException(sprintf('Cannot find attribute option with value "%s"', $value));
    }

    /**
     * Gets or creates a flexible value for attribute
     *
     * @param AbstractFlexible  $flexibleEntity
     * @param AbstractAttribute $attribute
     *
     * @return FlexibleValueInterface
     */
    private function getFlexibleValueForAttribute(AbstractFlexible $flexibleEntity, AbstractAttribute $attribute)
    {
        $flexibleValue = $flexibleEntity->getValue($attribute->getCode());
        if (!$flexibleValue) {
            $flexibleValue = $this->userManager->createFlexibleValue();
            $flexibleValue->setAttribute($attribute);
            $flexibleEntity->addValue($flexibleValue);
        }

        return $flexibleValue;
    }

    /**
     * Finds an attribute
     *
     * @param string $attributeCode
     *
     * @return AbstractAttribute
     */
    private function findAttribute($attributeCode)
    {
        return $this->userRepository->findAttributeByCode($attributeCode);
    }

    /**
     * Create an attribute
     *
     * @param string $attributeType
     * @param string $attributeCode
     *
     * @return AbstractAttribute
     */
    private function createAttribute($attributeType, $attributeCode)
    {
        $result = $this->userManager->createAttribute($attributeType);
        $result->setCode($attributeCode);
        $result->setLabel($attributeCode);

        return $result;
    }

    /**
     * Create an attribute with options
     *
     * @param string  $attributeType
     * @param string  $attributeCode
     * @param array   $optionValues
     * @param boolean $required
     * @param mixed   $label
     *
     * @return AbstractAttribute
     */
    private function createAttributeWithOptions($attributeType, $attributeCode, array $optionValues, $required = false, $label = false)
    {
        $attribute = $this->createAttribute($attributeType, $attributeCode);
        foreach ($optionValues as $value) {
            $attribute->addOption($this->createAttributeOptionWithValue($value));
            $attribute->setRequired($required);
            if ($label) {
                $attribute->setLabel($label);
            }
        }

        return $attribute;
    }

    /**
     * Create an attribute option with value
     *
     * @param string $value
     *
     * @return AbstractAttributeOption
     */
    private function createAttributeOptionWithValue($value)
    {
        $option = $this->userManager->createAttributeOption();
        $optionValue = $this->userManager->createAttributeOptionValue()->setValue($value);
        $option->addOptionValue($optionValue);

        return $option;
    }

    /**
     * Generates a username
     *
     * @param string $firstName
     * @param string $lastName
     *
     * @return string
     */
    private function generateUsername($firstName, $lastName)
    {
        $uniqueString = substr(uniqid(rand()), -5, 5);

        return sprintf("%s.%s_%s", strtolower($firstName), strtolower($lastName), $uniqueString);
    }

    /**
     * Generates an email
     *
     * @param string $firstName
     * @param string $lastName
     *
     * @return string
     */
    private function generateEmail($firstName, $lastName)
    {
        $uniqueString = substr(uniqid(rand()), -5, 5);
        $domains = array('yahoo.com', 'gmail.com', 'example.com', 'hotmail.com', 'aol.com', 'msn.com');
        $randomIndex = rand(0, count($domains) - 1);
        $domain = $domains[$randomIndex];

        return sprintf("%s.%s_%s@%s", strtolower($firstName), strtolower($lastName), $uniqueString, $domain);
    }

    /**
     * Generate a first name
     *
     * @return string
     */
    private function generateFirstName()
    {
        $firstNamesDictionary = $this->loadDictionary('first_names.txt');
        $randomIndex = rand(0, count($firstNamesDictionary) - 1);

        return trim($firstNamesDictionary[$randomIndex]);
    }

    /**
     * Generate a middle name
     *
     * @return string
     */
    private function generateMiddleName()
    {
        return $this->generateFirstName();
    }

    /**
     * Loads dictionary from file by name
     *
     * @param string $name
     *
     * @return array
     */
    private function loadDictionary($name)
    {
        static $dictionaries = array();

        if (!isset($dictionaries[$name])) {
            $dictionary = array();
            $fileName = __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
            foreach (file($fileName) as $item) {
                $dictionary[] = trim($item);
            }
            $dictionaries[$name] = $dictionary;
        }

        return $dictionaries[$name];
    }

    /**
     * Generates a last name
     *
     * @return string
     */
    private function generateLastName()
    {
        $lastNamesDictionary = $this->loadDictionary('last_names.txt');
        $randomIndex = rand(0, count($lastNamesDictionary) - 1);

        return trim($lastNamesDictionary[$randomIndex]);
    }

    /**
     * Generates a salary
     *
     * @return int
     */
    private function generateSalary()
    {
        return 12 * rand(4000, 30000);
    }

    /**
     * Generates a company name
     *
     * @return string
     */
    private function generateCompany()
    {
        $companyNamesDictionary = $this->loadDictionary('company_names.txt');
        $randomIndex = rand(0, count($companyNamesDictionary) - 1);

        return trim($companyNamesDictionary[$randomIndex]);
    }

    /**
     * Generates a date of birth
     *
     * @return \DateTime
     */
    private function generateBirthday()
    {
        // Convert to timetamps
        $min = strtotime('1950-01-01');
        $max = strtotime('2000-01-01');

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return new \DateTime(date('Y-m-d', $val));
    }

    /**
     * Generates a website
     *
     * @param string $firstName
     * @param string $lastName
     *
     * @return string
     */
    private function generateWebsite($firstName, $lastName)
    {
        $domain = 'example.com';

        return sprintf("http://%s%s.%s", strtolower($firstName), strtolower($lastName), $domain);
    }

    /**
     * Generates a gender
     *
     * @return string
     */
    private function generateGender()
    {
        $genders = array('Male', 'Female');

        return $genders[rand(0, 1)];
    }

    /**
     * Generates a locale
     *
     * @return string
     */
    private function generateLocale()
    {
        return 'en_US';
    }

    /**
     * Generates a scope
     *
     * @return string
     */
    private function generateScope()
    {
        return 'ecommerce';
    }

    /**
     * Generates hobbies
     *
     * @return string
     */
    private function generateHobbies()
    {
        $hobbies = self::getHobbies();
        $randomCount = rand(1, count($hobbies));
        shuffle($hobbies);

        return array_slice($hobbies, 0, $randomCount);
    }

    /**
     * Generates hobbies
     *
     * @return \DateTime
     */
    private function generateLastVisit()
    {
        $lastVisit = new \DateTime('now');
        $lastVisit->sub(new \DateInterval('P' . rand(1, 30) . 'D'));

        return $lastVisit;
    }

    /**
     * Get array of hobbies
     *
     * @return array
     */
    private static function getHobbies()
    {
        return array('Sport', 'Cooking', 'Read', 'Coding!');
    }

    /**
     * Get array of locales
     *
     * @return array
     */
    private static function getLocales()
    {
        return array('fr_FR', 'fr_CA', 'de_DE', 'en_US', 'en_GB');
    }

    /**
     * Get array of scopes
     *
     * @return array
     */
    private static function getScopes()
    {
        return array('ecommerce', 'mobile');
    }

    /**
     * Persist object
     *
     * @param mixed $object
     *
     * @return void
     */
    private function persist($object)
    {
        $this->userManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @return void
     */
    private function flush()
    {
        $this->userManager->getStorageManager()->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 112;
    }
}
