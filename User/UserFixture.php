<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\User;

use Magento\User\Api\Data\UserInterface;

class UserFixture
{
    /**
     * @var UserInterface
     */
    private UserInterface $user;

    /**
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function get(): UserInterface
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->user->getId();
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->user->getFirstName();
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->user->getLastName();
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->user->getEmail();
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->user->getUserName();
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->user->getPassword();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        UserFixtureRollback::create()->execute($this);
    }
}
