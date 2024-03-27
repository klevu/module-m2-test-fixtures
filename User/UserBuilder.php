<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\User;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\ResourceModel\User as UserResourceModel;

class UserBuilder
{
    /**
     * @var UserInterface
     */
    private UserInterface $user;

    /**
     * @param UserInterface $user
     */
    public function __construct(
        UserInterface $user,
    ) {
        $this->user = $user;
    }

    /**
     * @return UserBuilder
     */
    public static function addUser(): UserBuilder //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->create(UserInterface::class),
        );
    }

    /**
     * @param string $firstname
     *
     * @return UserBuilder
     */
    public function withFirstName(string $firstname): UserBuilder
    {
        $builder = clone $this;
        $builder->user->setFirstName($firstname);

        return $builder;
    }

    /**
     * @param string $lastname
     *
     * @return UserBuilder
     */
    public function withLastName(string $lastname): UserBuilder
    {
        $builder = clone $this;
        $builder->user->setLastName($lastname);

        return $builder;
    }

    /**
     * @param string $email
     *
     * @return UserBuilder
     */
    public function withEmail(string $email): UserBuilder
    {
        $builder = clone $this;
        $builder->user->setEmail($email);

        return $builder;
    }

    /**
     * @param string $username
     *
     * @return UserBuilder
     */
    public function withUserName(string $username): UserBuilder
    {
        $builder = clone $this;
        $builder->user->setUserName($username);

        return $builder;
    }

    /**
     * @param string $password
     *
     * @return UserBuilder
     */
    public function withPassword(string $password): UserBuilder
    {
        $builder = clone $this;
        $builder->user->setPassword($password);

        return $builder;
    }

    /**
     * @return UserInterface
     * @throws \Exception
     */
    public function build(): UserInterface
    {
        try {
            $builder = $this->createUser();

            return $this->saveUser($builder);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return UserInterface
     */
    public function buildWithoutSave(): UserInterface
    {
        $builder = $this->createUser();

        return $builder->user;
    }

    /**
     * @return UserBuilder
     */
    private function createUser(): UserBuilder
    {
        $builder = clone $this;
        if (!$builder->user->getFirstName()) {
            $builder->user->setFirstName('Admin');
        }
        if (!$builder->user->getLastName()) {
            $builder->user->setLastName('User');
        }
        if (!$builder->user->getEmail()) {
            $builder->user->setEmail('admin_user@klevu.com');
        }
        if (!$builder->user->getUserName()) {
            $builder->user->setUserName('admin');
        }
        if (!$builder->user->getPassword()) {
            $builder->user->setPassword('P@aS5w0rD');
        }

        return $builder;
    }

    /**
     * @param UserBuilder $builder
     *
     * @return UserInterface
     * @throws AlreadyExistsException
     */
    private function saveUser(UserBuilder $builder): UserInterface
    {
        // there is no user repository so revert to resourceModel
        /** @var UserResourceModel $storeResourceModel */
        $storeResourceModel = $this->user->getResource();
        $storeResourceModel->save($builder->user);

        return $builder->user;
    }

    /**
     * @param \Throwable|null $exception
     *
     * @return bool
     *
     */
    private static function isTransactionException( // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
        ?\Throwable $exception,
    ): bool {
        if ($exception === null) {
            return false;
        }

        return (bool)preg_match(
            '{please retry transaction|DDL statements are not allowed in transactions}i',
            $exception->getMessage(),
        );
    }
}
