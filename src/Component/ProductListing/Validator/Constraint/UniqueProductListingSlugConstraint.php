<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\Component\ProductListing\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class UniqueProductListingSlugConstraint extends Constraint
{
    public string $message = 'This value is already used.';

    private string $service = 'bitbag.open_marketplace.component.product_listing.validator.unique_product_listing_slug';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return $this->service;
    }
}
