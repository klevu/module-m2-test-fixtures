<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

trait GeneratorTrait
{
    /**
     * @param mixed[] $yieldValues
     *
     * @return \Generator
     */
    private function generate(array $yieldValues): \Generator
    {
        foreach ($yieldValues as $key => $value) {
            yield $key => $value;
        }
    }
}