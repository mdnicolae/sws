<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\OpenMarketplace\Behat\Page\Vendor;

use Behat\Mink\Element\NodeElement;

interface SettlementPageInterface
{
    public function openSettlementsIndex(): void;

    public function getSettlements(): array;

    public function findFirstAcceptButton(): ?NodeElement;

    public function getSettlementsWithStatus(string $status = null): array;

    public function filterByStatus(string $status): void;

    public function filterByPeriod(string $period): void;
}
