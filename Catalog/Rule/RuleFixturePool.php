<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Rule;

use Magento\CatalogRule\Api\Data\RuleInterface;

class RuleFixturePool
{
    /**
     * @var RuleFixture[]
     */
    private array $ruleFixtures = [];

    /**
     * @param RuleInterface $rule
     * @param string|null $key
     *
     * @return void
     */
    public function add(RuleInterface $rule, ?string $key = null): void
    {
        if ($key === null) {
            $this->ruleFixtures[] = new RuleFixture($rule);
        } else {
            $this->ruleFixtures[$key] = new RuleFixture($rule);
        }
    }

    /**
     * Returns store fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return RuleFixture
     */
    public function get(?string $key = null): RuleFixture
    {
        if ($key === null) {
            $key = array_key_last($this->ruleFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->ruleFixtures)) {
            throw new \OutOfBoundsException('No matching rule found in fixture pool');
        }

        return $this->ruleFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        RuleFixtureRollback::create()->execute(...array_values($this->ruleFixtures));
        $this->ruleFixtures = [];
    }
}
