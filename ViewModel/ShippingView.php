<?php

namespace PostNL\HyvaCheckout\ViewModel;

use Hyva\Checkout\ViewModel\Checkout\Formatter;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use TIG\PostNL\Helper\DeliveryOptions\PickupAddress;
use TIG\PostNL\Service\Carrier\ParcelTypeFinder;
use TIG\PostNL\Service\Carrier\Price\Calculator;
use TIG\PostNL\Service\Carrier\QuoteToRateRequest;
use function array_key_exists;
use function is_array;

class ShippingView implements ArgumentInterface
{
    private array $cache = [];

    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly Calculator $calculator,
        private readonly QuoteToRateRequest $quoteToRateRequest,
        private readonly Formatter $formatter
    ) {
    }

    public function getPriceFromAddressRequest(?string $parcel = null): array
    {
        $shipping = $this->checkoutSession->getQuote()->getShippingAddress();
        $key = $shipping->getCountryId() . '_' . $shipping->getPostcode() . $parcel;
        if (!array_key_exists($key, $this->cache)) {
            $request = $this->quoteToRateRequest->getByUpdatedAddress(
                (string) $shipping->getCountryId(),
                (string) $shipping->getPostcode()
            );
            $value = $this->calculator->getPriceWithTax($request, $parcel);
            // Format return types
            if (!is_array($value)) {
                $value = [];
            }
            $this->cache[$key] = $value;
        }

        return $this->cache[$key];
    }

    public function formatPrice(?float $price): string
    {
        if ($price === null) {
            return '';
        }

        return $this->formatter->currency($price);
    }

    public function getDeliveryPrice(): ?string
    {
        $price = $this->getPriceFromAddressRequest(ParcelTypeFinder::DEFAULT_TYPE);

        // With freeShipping price can be 0.0 here, so we need to make sure that array is correct
        return array_key_exists('price', $price) ? (string) $price['price'] : null;
    }

    public function getPickupPrice(): ?string
    {
        $price = $this->getPriceFromAddressRequest(PickupAddress::PG_ADDRESS_TYPE);

        return array_key_exists('price', $price) ? (string) $price['price'] : null;
    }

}
