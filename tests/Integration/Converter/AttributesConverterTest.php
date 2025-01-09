<?php

declare(strict_types=1);

namespace Tests\BitBag\OpenMarketplace\Integration\Converter;

use ApiTestCase\JsonApiTestCase;
use BitBag\OpenMarketplace\Component\Product\Entity\Product;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\Draft;

class AttributesConverterTest extends JsonApiTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->getContainer()
            ->get('doctrine')
            ->getManager()
        ;

        $this->attributesConverter = $this->getContainer()->get('bitbag.open_marketplace.component.product_listing.draft_converter.operator.attributes');
    }

    public function test_it_removes_attributes_from_product(): void
    {
        $this->loadFixturesFromFile('AttributesConverterTest/test_it_removes_attributes_from_product.yaml');
        $draft = $this->entityManager->getRepository(Draft::class)->findAll()[0];

        $productListing = $draft->getProductListing();
        $product = $productListing->getProduct();

        $this->assertCount(1, $product->getAttributes());

        $this->attributesConverter->convert($draft, $product);
        $this->entityManager->flush();

        $freshProduct = $this->entityManager->getRepository(Product::class)->findAll()[0];
        $this->entityManager->refresh($freshProduct);

        $this->assertCount(0, $freshProduct->getAttributes());
    }
}
