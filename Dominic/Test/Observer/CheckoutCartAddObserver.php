<?php
namespace Dominic\Test\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CheckoutCartAddObserver implements ObserverInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * CheckoutCartAddObserver constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Add vendor details to cart
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();
        $additionalOptions = [];
        if ($additionalOption = $item->getOptionByCode('additional_options')) {
            $additionalOptions = $this->serializer->unserialize($additionalOption->getValue());
        }

        $additionalOptions[] = [
            'label' => __('Vendor Name'),
            'value' => $product->getVendorName()
        ];

        $additionalOptions[] = [
            'label' => __('Vendor Phone number'),
            'value' => $product->getVendorMob()
        ];

        $additionalOptions[] = [
            'label' => __('Vendor City'),
            'value' => $product->getVendorCity()
        ];

        if (count($additionalOptions) > 0) {
            $item->addOption([
                'product_id' => $item->getProductId(),
                'code' => 'additional_options',
                'value' => $this->serializer->serialize($additionalOptions)
            ]);
        }
    }
}
