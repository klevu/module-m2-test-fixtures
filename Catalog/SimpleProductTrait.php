<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

/**
 * Wrapper for ProductTrait for backwards compatibility
 *
 * @deprecated Name changed to ProductTrait
 * @see \Klevu\TestFixtures\Catalog\ProductTrait
 */
trait SimpleProductTrait
{
    use ProductTrait;
}
