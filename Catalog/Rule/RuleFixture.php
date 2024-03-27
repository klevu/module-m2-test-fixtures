<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Rule;

use Klevu\TestFixtures\Exception\InvalidModelException;
use Magento\CatalogRule\Api\Data\RuleInterface;

class RuleFixture
{
    /**
     * @var RuleInterface
     */
    private RuleInterface $rule;

    /**
     * @param RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return RuleInterface
     */
    public function getRule(): RuleInterface
    {
        return $this->rule;
    }

    /**
     * @return int
     */
    public function getRuleId(): int
    {
        return (int)$this->rule->getRuleId();
    }

    /**
     * @return void
     * @throws InvalidModelException
     */
    public function rollback(): void
    {
        RuleFixtureRollback::create()->execute($this);
    }
}
