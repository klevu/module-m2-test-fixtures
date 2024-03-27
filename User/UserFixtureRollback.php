<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

//phpcs:disable Magento2.Annotation.MethodArguments.ArgumentMissing

namespace Klevu\TestFixtures\User;

use Klevu\TestFixtures\Exception\InvalidModelException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\ResourceModel\User as UserResourceModel;

class UserFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var UserInterface
     */
    private UserInterface $user;

    /**
     * @param Registry $registry
     * @param UserInterface $user
     */
    public function __construct(
        Registry $registry,
        UserInterface $user,
    ) {
        $this->registry = $registry;
        $this->user = $user;
    }

    /**
     * @return UserFixtureRollback
     */
    public static function create(): UserFixtureRollback // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(UserInterface::class),
        );
    }

    /**
     * Roll back users.
     *
     * @param UserFixture ...$userFixtures
     *
     * @throws InvalidModelException
     * @throws \Exception
     */
    public function execute(UserFixture ...$userFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($userFixtures as $userFixture) {
            try {
                $user = clone $this->user;
                if (!method_exists($user, 'getResource')) {
                    throw new InvalidModelException(
                        sprintf(
                            'Provided Model %s does not have require method %s.',
                            $user::class,
                            'getResource',
                        ),
                    );
                }
                // there is no user repository revert to resourceModel
                $userResourceModel = $user->getResource();
                if (!($userResourceModel instanceof UserResourceModel)) {
                    throw new InvalidModelException(
                        sprintf(
                            'Resource Model %s is not an instance of %s.',
                            $userResourceModel::class,
                            UserResourceModel::class,
                        ),
                    );
                }
                $userResourceModel->load($user, $userFixture->getId());
                $userResourceModel->delete($user);
            } catch (NoSuchEntityException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // user has already been removed
            }
        }

        $this->registry->unregister('isSecureArea');
    }
}
