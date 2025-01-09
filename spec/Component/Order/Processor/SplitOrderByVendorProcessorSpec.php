<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace spec\BitBag\OpenMarketplace\Component\Order\Processor;

use BitBag\OpenMarketplace\Component\Order\Entity\OrderInterface;
use BitBag\OpenMarketplace\Component\Order\Entity\OrderItemInterface;
use BitBag\OpenMarketplace\Component\Order\OrderManagerInterface;
use BitBag\OpenMarketplace\Component\Order\Processor\SplitOrderByVendorProcessor;
use BitBag\OpenMarketplace\Component\Vendor\Entity\VendorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SplitOrderByVendorProcessorSpec extends ObjectBehavior
{
    public function let(
        OrderManagerInterface $orderManager,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ): void {
        $this->beConstructedWith(
            $orderManager,
            $entityManager,
            $eventDispatcher
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SplitOrderByVendorProcessor::class);
    }

    public function it_creates_at_least_one_secondary_order_for_the_first_time(
        OrderInterface $order,
        PaymentInterface $payment,
        OrderItemInterface $orderItem,
        OrderInterface $subOrder,
        VendorInterface $vendor,
        OrderManagerInterface $orderManager,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $order->isPrimary()->willReturn(true);
        $order->getSecondaryOrders()->willReturn(new ArrayCollection([]));

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->willReturn((object) []);
        $orderItemCollection = new ArrayCollection([$orderItem->getWrappedObject()]);
        $paymentCollection = new ArrayCollection([$payment->getWrappedObject()]);
        $order->getItems()->willReturn($orderItemCollection);
        $orderItem->getProductOwner()->willReturn($vendor);
        $order->getPayments()->willReturn($paymentCollection);
        $order->getTotal()->willReturn(100);
        $subOrder->getVendor()->willReturn(null);
        $order->getVendor()->willReturn(null);
        $order->addSecondaryOrder(Argument::any())->shouldBeCalledTimes(1);
        $orderManager->generateNewSecondaryOrder($order, $vendor, $orderItem)->willReturn($subOrder);

        $this->process($order);

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    public function it_creates_2_secondary_orders_for_products_from_different_vendors(
        OrderInterface $order,
        PaymentInterface $payment,
        OrderItemInterface $orderItem,
        OrderItemInterface $secondItem,
        OrderInterface $subOrder,
        OrderInterface $subOrder2,
        VendorInterface $vendor,
        VendorInterface $vendor2,
        OrderManagerInterface $orderManager,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $order->isPrimary()->willReturn(true);
        $order->getSecondaryOrders()->willReturn(new ArrayCollection([]));

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->willReturn((object) []);
        $orderItemCollection = new ArrayCollection([$orderItem->getWrappedObject(), $secondItem->getWrappedObject()]);
        $paymentCollection = new ArrayCollection([$payment->getWrappedObject()]);
        $order->getItems()->willReturn($orderItemCollection);

        $orderItem->getProductOwner()->willReturn($vendor);
        $secondItem->getProductOwner()->willReturn($vendor2);
        $order->getPayments()->willReturn($paymentCollection);
        $order->getTotal()->willReturn(100);

        $order->getVendor()->willReturn(null);
        $subOrder->getVendor()->willReturn($vendor);
        $subOrder2->getVendor()->willReturn($vendor2);

        $orderManager->generateNewSecondaryOrder($order, $vendor, $orderItem)->willReturn($subOrder);
        $orderManager->generateNewSecondaryOrder($order, $vendor2, $secondItem)->willReturn($subOrder2);

        $this->process($order);

        $order->addSecondaryOrder(Argument::any())->shouldHaveBeenCalledTimes(2);
        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }
}
