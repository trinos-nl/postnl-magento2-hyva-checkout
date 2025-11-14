<?php
declare(strict_types=1);

namespace PostNL\FillIn\ViewModel;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use PostNL\FillIn\Model\Config;

class FillIn implements ArgumentInterface
{
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly CheckoutSession $checkoutSession,
        private readonly DesignInterface $design,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Config $config,
        private readonly UrlInterface $urlInterface
    ) {
    }

    /**
     * Get the default country configured in the Magento backend.
     */
    public function getDefaultCountry(): ?string
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if the current theme is Hyvä.
     */
    public function isHyvaTheme(): bool
    {
        $theme = $this->design->getDesignTheme();

        while ($theme !== null) {
            if (str_starts_with($theme->getCode(), 'Hyva/')) {
                return true;
            }
            $theme = $theme->getParentTheme();
        }

        return false;
    }

    public function canDisplay(string $blockName): bool
    {
        return $this->canDisplayInCart($blockName)
            || $this->canDisplayInMinicart($blockName)
            || $this->canDisplayInCheckout($blockName);
    }

    /**
     * Can display for cart
     */
    public function canDisplayInCart(string $layoutName): bool
    {
        if (!$this->config->isEnabledInCart()) {
            return false;
        }

        $configPosition = $this->getCartPosition();

        return $layoutName === $configPosition;
    }

    /**
     * Can display for minicart
     */
    public function canDisplayInMinicart(string $layoutName): bool
    {
        if (!$this->config->isEnabledInMiniCart()) {
            return false;
        }

        $configPosition = $this->getMinicartPosition();

        return $layoutName === $configPosition;
    }

    /**
     * Can display for checkout
     */
    public function canDisplayInCheckout(string $layoutName): bool
    {
        if (!$this->config->isEnabledInCheckout()) {
            return false;
        }

        $configPosition = $this->getCheckoutPosition();

        return $layoutName === $configPosition;
    }

    /**
     * Get URL for the FillIn button
     */
    public function getFillInUrl(): string
    {
        return $this->urlInterface->getUrl('postnl/fillin/index');
    }

    /**
     * Get the minicart position
     */
    public function getMinicartPosition(): string
    {
        return $this->config->getMiniCartDisplayPosition();
    }

    /**
     * Get the cart position
     */
    public function getCartPosition(): string
    {
        return $this->config->getCartDisplayPosition();
    }

    /**
     * Get the checkout position
     */
    public function getCheckoutPosition(): string
    {
        return $this->config->getCheckoutDisplayPosition();
    }

    /**
     * Check if a customer is logged in
     */
    public function isLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get the shipping country from the checkout session
     */
    public function getShippingCountry(): ?string
    {
        return $this->checkoutSession->getQuote()->getShippingAddress()?->getCountryId();
    }
}
