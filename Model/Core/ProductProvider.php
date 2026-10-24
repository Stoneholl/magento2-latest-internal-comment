<?php

declare(strict_types=1);

namespace Stoneholl\LatestInternalComment\Model\Core;

use Stoneholl\Core\Api\ProductProviderInterface;
use Stoneholl\Core\Model\Data\ProductDescriptor;

class ProductProvider implements ProductProviderInterface
{
    public function getProducts(): array
    {
        return [new ProductDescriptor(
            'latest-internal-comment',
            'Stoneholl Latest Internal Comment',
            ['Stoneholl_LatestInternalComment'],
            ['stoneholl/magento2-latest-internal-comment']
        )];
    }
}
