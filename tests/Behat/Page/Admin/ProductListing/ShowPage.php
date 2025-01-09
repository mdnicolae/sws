<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace Tests\BitBag\OpenMarketplace\Behat\Page\Admin\ProductListing;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

final class ShowPage extends SymfonyPage implements ShowPageInterface
{
    public function getRouteName(): string
    {
        return 'open_marketplace_admin_product_listing_show';
    }

    public function fillRejectMessage(string $message): void
    {
        $this->getDocument()
        ->fillField(
            'mvm_conversation[messages][__name__][content]',
            $message,
        );
    }
}
