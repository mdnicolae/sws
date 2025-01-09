<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\Component\Core\Admin\Controller\ProductListing;

use BitBag\OpenMarketplace\Component\Core\Common\StateMachine\ProductDraftStateMachineTransitionInterface;
use BitBag\OpenMarketplace\Component\ProductListing\DraftTransitions;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\DraftInterface;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\ListingInterface;
use BitBag\OpenMarketplace\Component\ProductListing\Repository\DraftRepositoryInterface;
use BitBag\OpenMarketplace\Component\ProductListing\Repository\ListingRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

final class RejectAction
{
    public function __construct(
        private ListingRepositoryInterface $productListingRepository,
        private RouterInterface $router,
        private ProductDraftStateMachineTransitionInterface $productDraftStateMachineTransition,
        private DraftRepositoryInterface $productDraftRepository,
        private RequestStack $requestStack
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        /** @var ListingInterface $productListing */
        $productListing = $this->productListingRepository->find($request->attributes->get('id'));

        /** @var DraftInterface $latestProductDraft */
        $latestProductDraft = $this->productDraftRepository->findLatestDraft($productListing);

        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $flashBag = $session->getFlashBag();

        if (
            $this
                ->productDraftStateMachineTransition
                ->can($latestProductDraft, DraftTransitions::TRANSITION_REJECT)
        ) {
            $this
                ->productDraftStateMachineTransition
                ->apply($latestProductDraft, DraftTransitions::TRANSITION_REJECT);

            $flashBag->add('success', 'open_marketplace.ui.product_listing_rejected');
        } else {
            $flashBag->add('success', 'open_marketplace.ui.product_listing_cannot_reject');
        }

        return new RedirectResponse($this->router->generate('open_marketplace_admin_product_listing_index'));
    }
}
