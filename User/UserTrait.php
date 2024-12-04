<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\User;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\Authorization\AdminSessionUserContext;

trait UserTrait
{
    /**
     * @var UserFixturesPool|null
     */
    private ?UserFixturesPool $userFixturesPool = null;

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

    /**
     * May require "@magentoAppIsolation enabled" if calling multiple times in the same test class
     *
     * @param UserInterface $user
     *
     * @return void
     */
    private function loginUser(UserInterface $user): void
    {
        $mockAdminSessionBuilder = $this->getMockBuilder(AdminSession::class);
        $mockAdminSessionBuilder->addMethods(['getUser', 'hasUser']);
        $mockAdminSession = $mockAdminSessionBuilder->disableOriginalConstructor()
            ->getMock();
        $mockAdminSession->method('hasUser')
            ->willReturn(true);
        $mockAdminSession->method('getUser')
            ->willReturn($user);

        $userContext = $this->objectManager->create(
            type: AdminSessionUserContext::class,
            arguments: [
                'adminSession' => $mockAdminSession,
            ],
        );
        $this->objectManager->addSharedInstance(
            instance: $userContext,
            className: AdminSessionUserContext::class,
        );
    }
}
