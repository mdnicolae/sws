<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\OpenMarketplace\Behat\Context\Ui\Vendor;

use Behat\Mink\Element\DocumentElement;
use Behat\MinkExtension\Context\RawMinkContext;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\Draft;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\DraftImage;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\DraftInterface;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\DraftTranslation;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\DraftTranslationInterface;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\Listing;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\ListingInterface;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\ListingPrice;
use BitBag\OpenMarketplace\Component\ProductListing\Entity\ListingPriceInterface;
use BitBag\OpenMarketplace\Component\Vendor\Entity\ShopUserInterface;
use BitBag\OpenMarketplace\Component\Vendor\Entity\Vendor;
use BitBag\OpenMarketplace\Component\Vendor\Entity\VendorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AdminUserExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ShopUserExampleFactory;
use Sylius\Component\Channel\Model\Channel;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Tests\BitBag\OpenMarketplace\Behat\Page\Vendor\ProductListing\EditPageInterface;
use Tests\BitBag\OpenMarketplace\Behat\Page\Vendor\ProductListing\IndexPageInterface;
use Webmozart\Assert\Assert;

final class ProductListingContext extends RawMinkContext
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ShopUserExampleFactory $shopUserExampleFactory,
        private FactoryInterface $vendorFactory,
        private SharedStorageInterface $sharedStorage,
        private AdminUserExampleFactory $adminUserExampleFactory,
        private string $imagePath,
        private FactoryInterface $localeFactory,
        private IndexPageInterface $productListingShowVendorPage,
        private EditPageInterface $productListingEditVendorPage,
        ) {
    }

    /**
     * @BeforeScenario
     */
    public function clearData()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    /**
     * @Given there is an :verified vendor user :username with password :password
     */
    public function thereIsAnVendorUserWithPassword(
        $verified,
        $username,
        $password
    ) {
        /** @var ShopUserInterface $user */
        $user = $this->shopUserExampleFactory->create();
        $user->setUsername($username);
        $user->setPlainPassword($password);
        $user->setEmail('vendor@email.com');
        $user->setVerifiedAt(new \DateTime());
        $user->addRole('ROLE_USER');
        $user->addRole('ROLE_VENDOR');
        $this->entityManager->persist($user);

        /** @var Vendor $vendor */
        $vendor = $this->vendorFactory->createNew();
        $vendor->setStatus($verified);
        $vendor->setCompanyName('vendor');
        $vendor->setShopUser($user);
        $vendor->setSlug('vendor-slug');
        $vendor->setDescription('description');
        $vendor->setPhoneNumber('987654321');
        $vendor->setTaxIdentifier('123456789');
        $vendor->setBankAccountNumber('iban');
        $this->entityManager->persist($vendor);

        $this->sharedStorage->set('vendor', $vendor);
        $this->entityManager->flush();
    }

    /**
     * @Given the product listing is removed
     */
    public function thereProductListingIsRemoved()
    {
        $productListing = $this->sharedStorage->get('product_listing');
        $productListing->setRemoved(true);
        $this->entityManager->persist($productListing);
        $this->entityManager->flush();
    }

    /**
     * @When I am on edit page product listing :url
     */
    public function iAmOnProductListingPageWithIUrl($url)
    {
        $productListing = $this->sharedStorage->get('product_listing');
        $this->productListingEditVendorPage->tryToOpen(['id' => $productListing->getId()]);
    }

    /**
     * @Given I should see product's listing status :status
     */
    public function iShouldSeeProductsListingStatus($status)
    {
        $productListingStatus = $this->productListingShowVendorPage->findStatus($status);
        Assert::notNull($productListingStatus);
    }

    /**
     * @Then I should see :count product listing(s)
     */
    public function iShouldSeeProductListings($count)
    {
        $rows = $this->productListingShowVendorPage->getTableRows();
        Assert::notEmpty($rows, 'Could not find any rows');
        Assert::eq($count, count($rows), 'Rows numbers are not equal');
    }

    /**
     * @Given I click :button button
     */
    public function iClickButton($button)
    {
        $this->getPage()->pressButton($button);
    }

    /**
     * @return DocumentElement
     */
    private function getPage()
    {
        return $this->getSession()->getPage();
    }

    /**
     * @Given there is :arg2 product listing created by vendor
     */
    public function thereIsProductListingCreatedByVendor(int $count): void
    {
        $this->createProductListingByVendor($count);
    }

    /**
     * @Given there is :count product listing created by vendor with status :status
     */
    public function thereIsProductListingCreatedByVendorWithStatus2(
        int $count,
        string $status,
    ): void {
        $this->createProductListingByVendor($count, $status);
    }

    /**
     * @Given Product listing status is :arg1
     */
    public function productListingStatusIs($arg1): void
    {
        $draft = $this->entityManager->getRepository(Draft::class)->findOneBy(['code' => 'code0']);
        $draft->setStatus(DraftInterface::STATUS_CREATED);
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }

    /**
     * @Then I should see dropdown with hide option
     */
    public function iShouldSeeDropdownWithHideOption(): void
    {
        $dropdown = $this->productListingShowVendorPage->findDropdownLink();
        Assert::notNull($dropdown);
    }

    /**
     * @Then I should see url :url
     */
    public function iShouldSeeUrl($url): void
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $matches = preg_match($url, $currentUrl);
        Assert::eq(1, $matches);
    }

    /**
     * @When I fill form with non unique code
     */
    public function iFillFormWithNonUniqueCode(): void
    {
        $page = $this->getPage();

        $page->fillField('Code', 'code0');
        $page->fillField('Price', '10');
        $page->fillField('Original price', '20');
        $page->fillField('Minimum price', '30');
        $page->fillField('Name', 'test');
        $page->fillField('Slug', 'product');
        $page->fillField('Description', 'product description');
    }

    /**
     * @Then I should see non unique code error message
     */
    public function iShouldSeeNonUniqueCodeMessage()
    {
        $text = $this->getPage()->getText();
        $isErrorMessagePresent = false !== stripos($text, 'Product Listing with given code already exists');
        Assert::true($isErrorMessagePresent);
    }

    /**
     * @Given I choose main taxon :taxon
     */
    public function iChooseMainTaxon($taxon)
    {
        $page = $this->getPage();
        $page->findById('sylius_product_mainTaxon')->setValue($taxon);
    }

    /**
     * @Then I should get validation error
     */
    public function iShouldGetValidationError()
    {
        $page = $this->getSession()->getPage();
        $this->getSession()->reload();

        $label = $page->find('css', '.ui.red.label.sylius-validation-error');
        Assert::eq($label->getText(), 'You must define price for every channel.');
    }

    /**
     * @Given there is an admin user :username with password :password
     */
    public function thereIsAnAdminUserWithPassword($username, $password)
    {
        $admin = $this->adminUserExampleFactory->create();
        $admin->setUsername($username);
        $admin->setPlainPassword($password);
        $admin->setEmail('admin@email.com');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $admin->setPlainPassword($password);
        $this->sharedStorage->set('admin', $admin);
    }

    /**
     * @Given I am logged in as an admin
     */
    public function iAmLoggedInAsAnAdmin()
    {
        $admin = $this->sharedStorage->get('admin');

        $this->visitPath('/admin/login');
        $this->getPage()->fillField('Username', $admin->getUsername());
        $this->getPage()->fillField('Password', $admin->getPlainPassword());
        $this->getPage()->pressButton('Login');
        $this->getPage()->findLink('Logout');
    }

    /**
     * @When I click :label on confirmation modal
     */
    public function iClickOnConfirmationModal(string $label): void
    {
        $confirmationModal = $this->getPage()->findById($label);
        $confirmationModal->click();
    }

    /**
     * @Given the channel uses another locale :locales
     */
    public function theChannelUsesAnotherLocale(string $locales): void
    {
        /** @var Channel $channel */
        $channel = $this->sharedStorage->get('channel');

        $locale = $this->localeFactory->createNew();
        $locale->setCode($locales);
        $channel->addLocale($locale);

        $this->entityManager->persist($locale);
        $this->entityManager->persist($channel);
        $this->entityManager->flush();
    }

    /**
     * @When I fill form with default data
     */
    public function iFillFormWithDefaultData(): void
    {
        $this->productListingEditVendorPage->fillFormWithDefaultData();
    }

    /**
     * @Given the product listing has an image attached
     */
    public function theProductListingHasAnImageAttached(): void
    {
        $productDraft = $this->sharedStorage->get('draft');

        file_put_contents($this->imagePath . '/test.jpg', '');
        $draftImage = new DraftImage();
        $draftImage->setOwner($productDraft);
        $draftImage->setPath('test.jpg');
        $productDraft->addImage($draftImage);

        $this->entityManager->persist($draftImage);
        $this->entityManager->persist($productDraft);

        $this->entityManager->flush();
    }

    /**
     * @Then I should see image
     */
    public function iShouldSeeImage(): void
    {
        $page = $this->getSession()->getPage();
        $mediaContainer = $page->find('css', '.ui.segments[data-tab="media"]');
        $image = $mediaContainer->find('css', 'img');
        $imagePath = $image->getAttribute('src');

        Assert::contains($imagePath, 'test.jpg', 'no image found');
    }

    /**
     * @Then I should not see image
     */
    public function iShouldNotSeeImage(): void
    {
        $page = $this->getSession()->getPage();
        $mediaContainer = $page->find('css', '.ui.segments[data-tab="media"]');
        Assert::notContains($mediaContainer->getHtml(), 'test.jpg', 'image found');
    }

    public function createProductListingByVendor($count, $status = 'under_verification'): void
    {
        $vendor = $this->sharedStorage->get('vendor');

        for ($i = 0; $i < $count; ++$i) {
            $productListing = $this->createProductListing(
                $vendor,
                'code' . $i
            );

            $productDraft = $this->createProductListingDraft(
                $productListing,
                'code' . $i,
                $status
            );

            $productTranslation = $this->createProductListingTranslation(
                $productDraft,
                'product-listing-name' . $i,
                'product-listing-description' . $i,
                'product-listing-slug' . $i
            );

            $productPricing = $this->createProductListingPricing(
                $productDraft
            );

            $this->entityManager->persist($productListing);
            $this->entityManager->persist($productDraft);
            $this->entityManager->persist($productTranslation);
            $this->entityManager->persist($productPricing);
            $this->sharedStorage->set('draft', $productDraft);

            $this->sharedStorage->set('product_listing', $productListing);
        }

        $this->entityManager->flush();
    }

    private function createProductListing(VendorInterface $vendor, string $code): ListingInterface
    {
        $productListing = new Listing();
        $productListing->setCode($code);
        $productListing->setVendor($vendor);

        return $productListing;
    }

    private function createProductListingDraft(
        ListingInterface $productListing,
        string $code = 'code',
        string $status = 'under_verification',
        int $versionNumber = 0,
        string $publishedAt = 'now'
    ): DraftInterface {
        $productDraft = new Draft();
        $productDraft->setCode($code);
        $productDraft->setStatus($status);
        $productDraft->setPublishedAt(new \DateTime($publishedAt));
        $productDraft->setVersionNumber($versionNumber);
        $productDraft->setProductListing($productListing);
        $channel = $this->getChannel();
        $productDraft->setChannels(new ArrayCollection([$channel]));

        return $productDraft;
    }

    private function createProductListingTranslation(
        DraftInterface $productDraft,
        string $name = 'product-listing-name',
        string $description = 'product-listing-description',
        string $slug = 'product-listing-slug',
        string $locale = 'en_US'
    ): DraftTranslationInterface {
        $productTranslation = new DraftTranslation();
        $productTranslation->setLocale($locale);
        $productTranslation->setSlug($slug);
        $productTranslation->setName($name);
        $productTranslation->setDescription($description);
        $productTranslation->setProductDraft($productDraft);

        return $productTranslation;
    }

    private function createProductListingPricing(
        DraftInterface $productDraft,
        int $price = 1000,
        int $originalPrice = 1000,
        int $minimumPrice = 1000,
        string $channelCode = 'web_us'
    ): ListingPriceInterface {
        $productPricing = new ListingPrice();
        $productPricing->setProductDraft($productDraft);
        $productPricing->setPrice($price);
        $productPricing->setOriginalPrice($originalPrice);
        $productPricing->setMinimumPrice($minimumPrice);
        $productPricing->setChannelCode($channelCode);

        return $productPricing;
    }

    private function getChannel(): ChannelInterface
    {
        return $this->entityManager->getRepository(ChannelInterface::class)
            ->findAll()[0];
    }
}
