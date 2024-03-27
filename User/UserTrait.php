<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\User;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;

trait UserTrait
{
    /**
     * @var UserFixturesPool|null
     */
    private ?UserFixturesPool $userFixturesPool;

    /**
     * @param string[]|null $userData
     *
     * @return void
     * @throws FixturePoolMissingException
     * @throws \Exception
     */
    private function createUser(?array $userData = []): void
    {
        if (null === $this->userFixturesPool) {
            throw new FixturePoolMissingException(
                'userFixturesPool has not been created in your test setUp method.',
            );
        }
        $userBuilder = UserBuilder::addUser();
        if (!empty($userData['firstname'])) {
            $userBuilder->withFirstName($userData['firstname']);
        }
        if (!empty($userData['lastname'])) {
            $userBuilder->withLastName($userData['lastname']);
        }
        if (!empty($userData['email'])) {
            $userBuilder->withEmail($userData['email']);
        }
        if (!empty($userData['username'])) {
            $userBuilder->withUserName($userData['username']);
        }
        if (!empty($userData['password'])) {
            $userBuilder->withPassword($userData['password']);
        }
        $this->userFixturesPool->add(
            $userBuilder->build(),
            $storeData['key'] ?? 'test_user',
        );
    }
}
