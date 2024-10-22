<?php

namespace App\Tests\Unit;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function testEntityIsValid(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $user = new User();
        $user->setEmail('albert.lecomte1989@gmail.com')
             ->setPassword('SecurePassword123!')
             ->setUsername('vehinys');

        $errors = $container->get('validator')->validate($user);
        $this->assertCount(0, $errors);
    }

    public function testInvalidUser()
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $user = new User();
        $user->setEmail('')
            ->setPassword('SecurePassword123!')
            ->setUsername('vehinys');

        $errors = $container->get('validator')->validate($user);
        $this->assertCount(1, $errors);
    }
}

