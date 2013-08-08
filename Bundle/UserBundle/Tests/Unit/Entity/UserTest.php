<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Status;
use Oro\Bundle\UserBundle\Entity\Email;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testUsername()
    {
        $user = new User;
        $name = 'Tony';

        $this->assertNull($user->getUsername());

        $user->setUsername($name);

        $this->assertEquals($name, $user->getUsername());
        $this->assertEquals($name, $user);
    }

    public function testEmail()
    {
        $user = new User;
        $mail = 'tony@mail.org';

        $this->assertNull($user->getEmail());

        $user->setEmail($mail);

        $this->assertEquals($mail, $user->getEmail());
    }

    public function testIsPasswordRequestNonExpired()
    {
        $user      = new User;
        $requested = new \DateTime('-10 seconds');

        $user->setPasswordRequestedAt($requested);

        $this->assertSame($requested, $user->getPasswordRequestedAt());
        $this->assertTrue($user->isPasswordRequestNonExpired(15));
        $this->assertFalse($user->isPasswordRequestNonExpired(5));
    }

    public function testIsPasswordRequestAtCleared()
    {
        $user = new User;
        $requested = new \DateTime('-10 seconds');

        $user->setPasswordRequestedAt($requested);
        $user->setPasswordRequestedAt(null);

        $this->assertFalse($user->isPasswordRequestNonExpired(15));
        $this->assertFalse($user->isPasswordRequestNonExpired(5));
    }

    public function testConfirmationToken()
    {
        $user  = new User;
        $token = $user->generateToken();

        $this->assertNotEmpty($token);

        $user->setConfirmationToken($token);

        $this->assertEquals($token, $user->getConfirmationToken());
    }

    public function testSetRolesWithArrayArgument()
    {
        $roles = array(new Role(User::ROLE_DEFAULT));
        $user = new User;
        $this->assertEmpty($user->getRoles());
        $user->setRoles($roles);
        $this->assertEquals($roles, $user->getRoles());
    }

    public function testSetRolesWithCollectionArgument()
    {
        $roles = new ArrayCollection(array(new Role(User::ROLE_DEFAULT)));
        $user = new User;
        $this->assertEmpty($user->getRoles());
        $user->setRoles($roles);
        $this->assertEquals($roles->toArray(), $user->getRoles());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $roles must be an instance of Doctrine\Common\Collections\Collection or an array
     */
    public function testSetRolesThrowsInvalidArgumentException()
    {
        $user = new User;
        $user->setRoles('roles');
    }

    public function testHasRoleWithStringArgument()
    {
        $user = new User;
        $role = new Role(User::ROLE_DEFAULT);

        $this->assertFalse($user->hasRole(User::ROLE_DEFAULT));
        $user->addRole($role);
        $this->assertTrue($user->hasRole(User::ROLE_DEFAULT));
    }

    public function testHasRoleWithObjectArgument()
    {
        $user = new User;
        $role = new Role(User::ROLE_DEFAULT);

        $this->assertFalse($user->hasRole($role));
        $user->addRole($role);
        $this->assertTrue($user->hasRole($role));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string
     */
    public function testHasRoleThrowsInvalidArgumentException()
    {
        $user = new User;
        $user->hasRole(new \stdClass());
    }

    public function testRemoveRoleWithStringArgument()
    {
        $user = new User;
        $role = new Role(User::ROLE_DEFAULT);
        $user->addRole($role);

        $this->assertTrue($user->hasRole($role));
        $user->removeRole(User::ROLE_DEFAULT);
        $this->assertFalse($user->hasRole($role));
    }

    public function testRemoveRoleWithObjectArgument()
    {
        $user = new User;
        $role = new Role(User::ROLE_DEFAULT);
        $user->addRole($role);

        $this->assertTrue($user->hasRole($role));
        $user->removeRole($role);
        $this->assertFalse($user->hasRole($role));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string
     */
    public function testRemoveRoleThrowsInvalidArgumentException()
    {
        $user = new User;
        $user->removeRole(new \stdClass());
    }

    public function testSetRolesCollection()
    {
        $user = new User;
        $role = new Role(User::ROLE_DEFAULT);
        $roles = new ArrayCollection(array($role));
        $user->setRolesCollection($roles);
        $this->assertSame($roles, $user->getRolesCollection());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $collection must be an instance of Doctrine\Common\Collections\Collection
     */
    public function testSetRolesCollectionThrowsException()
    {
        $user = new User();
        $user->setRolesCollection(array());
    }

    public function testGroups()
    {
        $user  = new User;
        $role  = new Role('ROLE_FOO');
        $group = new Group('Users');

        $group->addRole($role);

        $this->assertNotContains($role, $user->getRoles());

        $user->addGroup($group);

        $this->assertContains($group, $user->getGroups());
        $this->assertContains('Users', $user->getGroupNames());
        $this->assertTrue($user->hasRole($role));
        $this->assertTrue($user->hasGroup('Users'));

        $user->removeGroup($group);

        $this->assertFalse($user->hasRole($role));
    }

    public function testIsEnabled()
    {
        $user = new User;

        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertTrue($user->isAccountNonLocked());

        $user->setEnabled(false);

        $this->assertFalse($user->isEnabled());
        $this->assertFalse($user->isAccountNonLocked());
    }

    public function testSerializing()
    {
        $user  = new User;
        $clone = clone $user;
        $data  = $user->serialize();

        $this->assertNotEmpty($data);

        $user->setPassword('newpass')
             ->setConfirmationToken('token')
             ->setUsername('newname');

        $user->unserialize($data);

        $this->assertEquals($clone, $user);
    }

    public function testPassword()
    {
        $user = new User;
        $pass = 'anotherPassword';

        $user->setPassword($pass);
        $user->setPlainPassword($pass);

        $this->assertEquals($pass, $user->getPassword());
        $this->assertEquals($pass, $user->getPlainPassword());

        $user->eraseCredentials();

        $this->assertNull($user->getPlainPassword());
    }

    public function testCallbacks()
    {
        $user = new User;
        $user->beforeSave();
        $this->assertInstanceOf('\DateTime', $user->getCreatedAt());
    }

    public function testStatuses()
    {
        $user  = new User;
        $status  = new Status();

        $this->assertNotContains($status, $user->getStatuses());
        $this->assertNull($user->getCurrentStatus());

        $user->addStatus($status);
        $user->setCurrentStatus($status);

        $this->assertContains($status, $user->getStatuses());
        $this->assertEquals($status, $user->getCurrentStatus());

        $user->setCurrentStatus();

        $this->assertNull($user->getCurrentStatus());

        $user->getStatuses()->clear();

        $this->assertNotContains($status, $user->getStatuses());
    }

    public function testEmails()
    {
        $user  = new User;
        $email  = new Email();

        $this->assertNotContains($email, $user->getEmails());

        $user->addEmail($email);

        $this->assertContains($email, $user->getEmails());

        $user->removeEmail($email);

        $this->assertNotContains($email, $user->getEmails());
    }

    public function testNames()
    {
        $user  = new User();
        $first = 'James';
        $last  = 'Bond';

        $user->setFirstname($first);
        $user->setLastname($last);

        $this->assertEquals($user->getFullname(), sprintf('%s %s', $first, $last));

        $user->setNameFormat('%last%, %first%');

        $this->assertEquals($user->getFullname(), sprintf('%s, %s', $last, $first));
    }

    public function testDates()
    {
        $user = new User;
        $now  = new \DateTime('-1 year');

        $user->setBirthday($now);
        $user->setLastLogin($now);

        $this->assertEquals($now, $user->getBirthday());
        $this->assertEquals($now, $user->getLastLogin());
    }

    public function testApi()
    {
        $user = new User;
        $api  = new UserApi();

        $this->assertNull($user->getApi());

        $user->setApi($api);

        $this->assertEquals($api, $user->getApi());
    }

    public function testImage()
    {
        $user = new User;

        $this->assertNull($user->getImagePath());

        $user->setImage('test');

        $this->assertEquals('test', $user->getImage());
        $this->assertNotEmpty($user->getUploadDir());
        $path = $user->getUploadDir(true) . '/' . $user->getImage();
        $this->assertEquals($path, $user->getImagePath());
    }

    public function testUnserialize()
    {
        $user = new User();
        $serialized = array(
            'password',
            'salt',
            'username',
            true,
            'confirmation_token',
            10
        );
        $user->unserialize(serialize($serialized));

        $this->assertEquals($serialized[0], $user->getPassword());
        $this->assertEquals($serialized[1], $user->getSalt());
        $this->assertEquals($serialized[2], $user->getUsername());
        $this->assertEquals($serialized[3], $user->isEnabled());
        $this->assertEquals($serialized[4], $user->getConfirmationToken());
        $this->assertEquals($serialized[5], $user->getId());
    }

    public function testImageFile()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $user = new User();
        $this->assertSame($user, $user->setImageFile($file));
        $this->assertInstanceOf('\DateTime', $user->getUpdated());
        $this->assertEquals($user->getUpdated(), $user->getUpdatedAt());
        $this->assertEquals($file, $user->getImageFile());
        $this->assertSame($user, $user->unsetImageFile());
        $this->assertNull($user->getImageFile());
    }

    public function testIsCredentialsNonExpired()
    {
        $user = new User();
        $this->assertTrue($user->isCredentialsNonExpired());
    }

    /**
     * @dataProvider provider
     * @param string $property
     * @param mixed  $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new User();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array('username', 'test'),
            array('email', 'test'),
            array('firstname', 'test'),
            array('lastname', 'test'),
            array('birthday', new \DateTime()),
            array('image', 'test'),
            array('password', 'test'),
            array('plainPassword', 'test'),
            array('confirmationToken', 'test'),
            array('passwordRequestedAt', new \DateTime()),
            array('lastLogin', new \DateTime()),
            array('loginCount', 11),
            array('created', new \DateTime()),
            array('updated', new \DateTime()),
            array('userOwner', new User()),
            array('businessUnitOwners', new ArrayCollection(array(new BusinessUnit()))),
            array('organizationOwners', new ArrayCollection(array(new Organization()))),
        );
    }

    public function testPreUpdate()
    {
        $user = new User();
        $user->preUpdate();
        $this->assertInstanceOf('\DateTime', $user->getUpdated());
    }

    public function testBusinessUnit()
    {
        $user  = new User;
        $businessUnit = new BusinessUnit();

        $user->setBusinessUnits(new ArrayCollection(array($businessUnit)));

        $this->assertContains($businessUnit, $user->getBusinessUnits());

        $user->removeBusinessUnit($businessUnit);

        $this->assertNotContains($businessUnit, $user->getBusinessUnits());

        $user->addBusinessUnit($businessUnit);

        $this->assertContains($businessUnit, $user->getBusinessUnits());
    }
}
