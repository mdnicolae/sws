<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\Component\Settlement\Creator;

use BitBag\OpenMarketplace\Component\Settlement\Entity\SettlementInterface;
use BitBag\OpenMarketplace\Component\Vendor\Entity\VendorInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface SettlementCreatorInterface
{
    public function createSettlementsForAutoGeneration(
        VendorInterface $vendor,
        array $channels
    ): array;

    public function createSettlementForWithdrawal(
        VendorInterface $vendor,
        ChannelInterface $channel,
        int $amount,
        ): SettlementInterface;
}
