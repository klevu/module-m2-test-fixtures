<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\User;

use Magento\User\Api\Data\UserInterface;

class UserFixturesPool
{
    /**
     * @var UserFixture[]
     */
    private array $userFixtures = [];

    /**
     * @param UserInterface $user
     * @param string|null $key
     *
     * @return void
     */
    public function add(UserInterface $user, ?string $key = null): void
    {
        if ($key === null) {
            $this->userFixtures[] = new UserFixture($user);
        } else {
            $this->userFixtures[$key] = new UserFixture($user);
        }
    }

    /**
     * Returns user fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return UserFixture
     */
    public function get(?string $key = null): UserFixture
    {
        if ($key === null) {
            $key = array_key_last($this->userFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->userFixtures)) {
            throw new \OutOfBoundsException('No matching user found in fixture pool');
        }

        return $this->userFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        UserFixtureRollback::create()->execute(...array_values($this->userFixtures));
        $this->userFixtures = [];
    }
}
